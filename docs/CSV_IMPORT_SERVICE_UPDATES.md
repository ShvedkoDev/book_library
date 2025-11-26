# CSV Import Service Updates

## Overview
This document outlines the updates needed to the CSV import service to handle new database fields from the main-master-table.csv structure.

---

## Changes Required

### 1. **Book Fields Handling**

#### In `BookCsvImportService.php` - `createOrUpdateBook()` method

Add these new fields to the book data array:

```php
'notes_version' => $rowData['Notes related to version.'] ?? null,
'abstract' => $rowData['ABSTRACT'] ?? null,
// Update 'description' to use new DESCRIPTION column instead of combined
'description' => $rowData['DESCRIPTION'] ?? null,
```

**Note**: The old CSV had "ABSTRACT/DESCRIPTION" as one field. New CSV has separate columns:
- Column AU: "DESCRIPTION"
- Column AV: "ABSTRACT"

---

### 2. **Book Relationships - New 'translated' Type**

#### In `BookCsvImportRelationships.php` - `createBookRelationships()` method

Update the relationship type mapping to handle the new "Related (translated)" column:

```php
// OLD CSV Column Q: "Related (same title, different language, or similar)"
// NEW CSV Column Q: "Related (translated)"

$relationshipMappings = [
    'Related (same)' => BookRelationship::TYPE_SAME_VERSION,
    'Related (omnibus)' => BookRelationship::TYPE_SAME_LANGUAGE,
    'Related (support)' => BookRelationship::TYPE_SUPPORTING,
    'Related (translated)' => BookRelationship::TYPE_TRANSLATED,  // NEW
    // Keep for backward compatibility:
    'Related (same title, different language, or similar)' => BookRelationship::TYPE_OTHER_LANGUAGE,
];
```

---

### 3. **Library References - New Link Fields**

#### In `BookCsvImportRelationships.php` - `createLibraryReferences()` method

Update to handle 10 new library link columns (BH-BQ):

```php
// New structure: 5 libraries × 2 links (main + alt) = 10 columns

$libraryMappings = [
    // UH - University of Hawaii
    [
        'code' => 'UH',
        'name' => 'University of Hawaii',
        'main_link' => 'Library link UH',           // Column BH
        'alt_link' => 'Library link UH alt.',       // Column BI
        'reference' => 'UH hard copy ref',
        'call_number' => 'UH hard copy call number',
        'catalog_link' => 'UH hard copy link',
        'notes' => 'UH note',
    ],
    // COM-FSM - College of Micronesia
    [
        'code' => 'COM-FSM',
        'name' => 'College of Micronesia - FSM',
        'main_link' => 'Library link COM-FSM',      // Column BJ
        'alt_link' => 'Library link COM-FSM alt.',  // Column BK
        'reference' => 'COM hard copy ref',
        'call_number' => 'COM hard copy call number',
        'notes' => 'COM hard copy ref NOTE',
    ],
    // MARC - University of Guam
    [
        'code' => 'MARC',
        'name' => 'University of Guam (MARC)',
        'main_link' => 'Library link MARC',         // Column BL
        'alt_link' => 'Library link MARC alt.',     // Column BM
    ],
    // MICSEM - Micronesian Seminar
    [
        'code' => 'MICSEM',
        'name' => 'Micronesian Seminar',
        'main_link' => 'Library link MICSEM',       // Column BN
        'alt_link' => 'Library link MICSEM alt.',   // Column BO
    ],
    // LIB5 - Reserved Library #5
    [
        'code' => 'LIB5',
        'name' => 'Library #5 (Reserved)',
        'main_link' => 'Library link 5',            // Column BP
        'alt_link' => 'Library link 5 alt.',        // Column BQ
    ],
];

// Process each library
foreach ($libraryMappings as $library) {
    $hasData = !empty($rowData[$library['reference'] ?? ''] ?? null)
        || !empty($rowData[$library['call_number'] ?? ''] ?? null)
        || !empty($rowData[$library['catalog_link'] ?? ''] ?? null)
        || !empty($rowData[$library['main_link'] ?? ''] ?? null)
        || !empty($rowData[$library['alt_link'] ?? ''] ?? null);

    if ($hasData) {
        LibraryReference::create([
            'book_id' => $book->id,
            'library_code' => $library['code'],
            'library_name' => $library['name'],
            'reference_number' => $rowData[$library['reference'] ?? ''] ?? null,
            'call_number' => $rowData[$library['call_number'] ?? ''] ?? null,
            'catalog_link' => $rowData[$library['catalog_link'] ?? ''] ?? null,
            'main_link' => $rowData[$library['main_link'] ?? ''] ?? null,     // NEW
            'alt_link' => $rowData[$library['alt_link'] ?? ''] ?? null,       // NEW
            'notes' => $rowData[$library['notes'] ?? ''] ?? null,
        ]);
    }
}
```

---

### 4. **Book Identifiers - New Table**

#### Create new method in `BookCsvImportRelationships.php`:

```php
/**
 * Create book identifiers (OCLC, ISBN, Other)
 *
 * @param Book $book
 * @param array $rowData
 * @return void
 */
protected function createBookIdentifiers(Book $book, array $rowData): void
{
    // OCLC Number (Column BR)
    if (!empty($rowData['OLLC number'])) {
        BookIdentifier::create([
            'book_id' => $book->id,
            'identifier_type' => BookIdentifier::TYPE_OCLC,
            'identifier_value' => trim($rowData['OLLC number']),
        ]);
    }

    // ISBN Number (Column BS)
    if (!empty($rowData['ISBN number'])) {
        $isbn = trim($rowData['ISBN number']);

        // Determine if it's ISBN-10 or ISBN-13
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);
        $type = strlen($cleanIsbn) === 13 ? BookIdentifier::TYPE_ISBN13 : BookIdentifier::TYPE_ISBN;

        BookIdentifier::create([
            'book_id' => $book->id,
            'identifier_type' => $type,
            'identifier_value' => $isbn,
        ]);
    }

    // Other Number (Column BT)
    if (!empty($rowData['Other number'])) {
        BookIdentifier::create([
            'book_id' => $book->id,
            'identifier_type' => BookIdentifier::TYPE_OTHER,
            'identifier_value' => trim($rowData['Other number']),
        ]);
    }
}
```

#### Call this method in `importRow()` method:

```php
// After creating library references
$this->createBookIdentifiers($book, $rowData);
```

---

## CSV Column Mapping Reference

### New/Changed Columns

| Column | CSV Header | Database Field | Notes |
|--------|-----------|----------------|-------|
| Q | Related (translated) | book_relationships.relationship_type = 'translated' | Changed from "different language" |
| AS | Notes related to version. | books.notes_version | New field |
| AU | DESCRIPTION | books.description | Now separate from abstract |
| AV | ABSTRACT | books.abstract | Now separate from description |
| BH | Library link UH | library_references.main_link (UH) | New field |
| BI | Library link UH alt. | library_references.alt_link (UH) | New field |
| BJ | Library link COM-FSM | library_references.main_link (COM-FSM) | New field |
| BK | Library link COM-FSM alt. | library_references.alt_link (COM-FSM) | New field |
| BL | Library link MARC | library_references.main_link (MARC) | New library |
| BM | Library link MARC alt. | library_references.alt_link (MARC) | New library |
| BN | Library link MICSEM | library_references.main_link (MICSEM) | New library |
| BO | Library link MICSEM alt. | library_references.alt_link (MICSEM) | New library |
| BP | Library link 5 | library_references.main_link (LIB5) | New library |
| BQ | Library link 5 alt. | library_references.alt_link (LIB5) | New library |
| BR | OLLC number | book_identifiers (type: oclc) | New table/field |
| BS | ISBN number | book_identifiers (type: isbn/isbn13) | New table/field |
| BT | Other number | book_identifiers (type: other) | New table/field |

---

## Testing Checklist

After implementing these changes, test:

- [ ] Import CSV with new "Related (translated)" relationships
- [ ] Verify `notes_version` field populates correctly
- [ ] Verify separate `description` and `abstract` fields
- [ ] Verify all 5 libraries import with main_link and alt_link
- [ ] Verify OCLC, ISBN, and Other identifiers are created
- [ ] Test that MARC, MICSEM, and LIB5 library references are created
- [ ] Backward compatibility: old CSV format still works (if needed)

---

**Last Updated**: 2025-11-26
**Status**: Ready for Implementation
