# Bulk Editing Interface - Implementation TODO

**Feature Reference**: Implement spreadsheet-style bulk editing interface (from FEEDBACK_TODO.md)

**Primary Goal**: Enable mass edits for scenarios like correcting misspelled publisher names across 100+ books, updating collection names, categories, and any shared field across a wide range of books simultaneously.

**Success Criteria**: Reduce task time from "filling every bit of information again and again" to "1 minute like spreadsheet"

---

## 📋 **OVERVIEW**

### Problem Statement
Current one-by-one editing is "very slow and tedious". Example scenario: adding 10 books from same series requires filling every field 10 times, taking significant time compared to spreadsheet entry.

### Solution Requirements
- Spreadsheet-like interface for bulk data entry and editing
- Inline cell editing with immediate feedback
- Bulk operations (find & replace, copy-paste, fill down)
- Fast, responsive interface handling 100+ books
- Validation and error handling
- Integration with existing FilamentPHP admin panel

---

## 🎯 **PHASE 1: RESEARCH & PLANNING** (Week 1)

### 1.1 Technical Research
- [ ] **Research Filament table editing capabilities**
  - Investigate Filament's native inline editing features
  - Check if Filament supports editable table columns
  - Review Filament documentation for bulk editing patterns
  - **Resource**: https://filamentphp.com/docs/tables

- [ ] **Evaluate frontend grid libraries**
  - [ ] **Option A: Handsontable** (Spreadsheet-like)
    - Pros: Excel-like UI, copy-paste, fill-down, familiar UX
    - Cons: Commercial license for business use
    - **Resource**: https://handsontable.com

  - [ ] **Option B: AG-Grid** (Enterprise-grade)
    - Pros: High performance, cell editing, Excel export
    - Cons: Commercial license for advanced features
    - **Resource**: https://www.ag-grid.com

  - [ ] **Option C: Tabulator** (Open source)
    - Pros: Free, lightweight, editable cells, Excel export
    - Cons: Less Excel-like than Handsontable
    - **Resource**: http://tabulator.info

  - [ ] **Option D: Custom Alpine.js + Tailwind Grid**
    - Pros: Full control, matches existing stack, no licensing
    - Cons: More development time, need to implement features

  - [ ] **Option E: Laravel Livewire Tables with inline editing**
    - Pros: Native Laravel integration, reactive updates
    - Cons: May not feel as "spreadsheet-like"

- [ ] **Research copy-paste functionality**
  - Clipboard API for reading pasted data
  - Parsing Excel/CSV data from clipboard
  - Multi-cell selection and paste

- [ ] **Research validation strategies**
  - Client-side validation for immediate feedback
  - Server-side validation for data integrity
  - Batch validation for multiple rows

### 1.2 Architecture Planning
- [ ] **Define data model for bulk editing**
  - Which Book fields are editable in bulk?
  - Which relationships can be edited? (Publishers, Collections, Languages, etc.)
  - Which fields require special handling? (slugs, file uploads, etc.)

- [ ] **Design API endpoints**
  - `GET /api/admin/books/bulk` - Fetch paginated books for editing
  - `PATCH /api/admin/books/bulk` - Update multiple books
  - `POST /api/admin/books/validate-bulk` - Validate changes before saving
  - `GET /api/admin/books/export` - Export to CSV/Excel
  - `POST /api/admin/books/import` - Import from CSV/Excel

- [ ] **Plan database optimization**
  - Identify indexes needed for bulk queries
  - Plan transaction strategy for bulk updates
  - Consider using Laravel's `updateOrCreate` in batches

### 1.3 Define Editable Fields Priority
- [ ] **Core fields (highest priority)**
  - `title` - Main book title
  - `subtitle` - Book subtitle
  - `translated_title` - Translated title
  - `publication_year` - Year published
  - `pages` - Page count
  - `access_level` - Full/Limited/Unavailable
  - `is_featured` - Featured flag
  - `is_active` - Active flag
  - `physical_type` - Book/Journal/Magazine/etc.

- [ ] **Relationship fields (high priority)**
  - `publisher_id` - Publisher (dropdown/autocomplete)
  - `collection_id` - Collection (dropdown/autocomplete)
  - `languages` - Languages (multi-select)
  - `creators` - Authors/Illustrators (multi-select)

- [ ] **Text fields (medium priority)**
  - `description` - Book description
  - `notes_issue` - Issue notes
  - `notes_content` - Content notes
  - `contact` - Contact info

- [ ] **Classification fields (medium priority)**
  - Purpose, Genre, Sub-genre, Type, Themes, Learner Level

- [ ] **Advanced fields (low priority, maybe exclude)**
  - `internal_id`, `palm_code` (identifiers, rarely changed in bulk)
  - File uploads (not suitable for bulk editing)
  - Book relationships (complex, maybe exclude)

---

## 🛠️ **PHASE 2: BACKEND IMPLEMENTATION** (Week 2-3)

### 2.1 Create Bulk Editing Service
- [ ] **Create `BulkEditingService` class**
  - File: `app/Services/BulkEditingService.php`
  - Methods:
    - `getBooksForBulkEdit($filters, $pagination)`
    - `validateBulkChanges($changes)`
    - `applyBulkChanges($changes)`
    - `bulkUpdate($bookIds, $fieldName, $value)`
    - `findAndReplace($bookIds, $fieldName, $search, $replace)`

- [ ] **Implement transaction-based bulk updates**
  ```php
  DB::transaction(function () use ($changes) {
      foreach ($changes as $bookId => $fields) {
          Book::find($bookId)->update($fields);
      }
  });
  ```

- [ ] **Add batch validation logic**
  - Validate all changes before applying
  - Return detailed error messages per row/field
  - Handle relationship validation (foreign keys)

### 2.2 Create API Endpoints
- [ ] **Create `BulkEditingController`**
  - File: `app/Http/Controllers/Admin/BulkEditingController.php`
  - Route prefix: `/admin/api/bulk-editing`

- [ ] **Implement API endpoints**
  - [ ] `GET /admin/api/bulk-editing/books`
    - Return paginated books with all editable fields
    - Accept filters (language, publisher, collection, etc.)
    - Include related data (publishers, collections, languages)

  - [ ] `PATCH /admin/api/bulk-editing/books`
    - Accept array of changes: `[{id: 1, title: "New Title", ...}, ...]`
    - Validate all changes
    - Apply updates in transaction
    - Return success/error status per book

  - [ ] `POST /admin/api/bulk-editing/validate`
    - Validate proposed changes without saving
    - Return validation errors per field

  - [ ] `POST /admin/api/bulk-editing/bulk-update`
    - Update specific field across multiple books
    - Payload: `{book_ids: [1,2,3], field: "publisher_id", value: 5}`

  - [ ] `POST /admin/api/bulk-editing/find-replace`
    - Find and replace text in specific field
    - Payload: `{book_ids: [1,2,3], field: "publisher", search: "Micronsia", replace: "Micronesia"}`

- [ ] **Add authentication & authorization**
  - Ensure only admin users can access bulk editing
  - Use Laravel policies or Filament authorization

### 2.3 CSV Import/Export Integration
- [ ] **Create CSV export functionality**
  - Use Laravel Excel or Maatwebsite/Excel package
  - Export all books or filtered subset
  - Include all editable fields
  - Map relationship IDs to readable names

- [ ] **Create CSV import functionality**
  - Parse uploaded CSV file
  - Map columns to database fields
  - Validate imported data
  - Create preview of changes before applying
  - Handle relationship fields (match by name, create if not exists)

- [ ] **Add CSV field mapping interface**
  - Allow admin to map CSV columns to database fields
  - Save mapping templates for reuse
  - Handle common variations (e.g., "Year" = "publication_year")

### 2.4 Database Optimization
- [ ] **Add necessary indexes**
  - Index frequently filtered fields (publisher_id, collection_id, access_level)
  - Consider composite indexes for common queries

- [ ] **Optimize bulk queries**
  - Use eager loading for relationships
  - Implement pagination for large datasets
  - Consider chunking for very large updates (1000+ books)

---

## 💻 **PHASE 3: FRONTEND IMPLEMENTATION** (Week 3-4)

### 3.1 Create Filament Bulk Editing Page
- [ ] **Create new Filament page**
  - File: `app/Filament/Pages/BulkEditBooks.php`
  - Add to navigation: "Bulk Edit" under "Library" group
  - Icon: `heroicon-o-table-cells`

- [ ] **Design page layout**
  - Top toolbar: Filters, actions, save button, export/import
  - Main area: Editable grid
  - Bottom toolbar: Pagination, row count

### 3.2 Choose and Integrate Grid Library
- [ ] **Decision: Select grid library based on research**
  - Document decision and rationale
  - Install chosen library via npm/yarn
  - Configure build process (Vite)

- [ ] **Integrate grid library with Livewire**
  - Set up two-way data binding
  - Handle grid events (cell changed, row selected, etc.)
  - Implement save/cancel logic

### 3.3 Implement Core Editing Features

#### 3.3.1 Basic Cell Editing
- [ ] **Enable inline cell editing**
  - Click to edit text fields
  - Dropdown for select fields (publisher, collection, access_level)
  - Multi-select for relationships (languages, creators)
  - Date picker for publication_year
  - Toggle for boolean fields (is_featured, is_active)

- [ ] **Implement cell validation**
  - Real-time validation as user types
  - Visual indicators (red border for errors, green for valid)
  - Show validation error messages on hover

- [ ] **Auto-save vs Manual save**
  - **Option A**: Auto-save after each cell edit
  - **Option B**: Track changes, save all on button click
  - **Recommendation**: Option B (better for batch operations, less server load)

#### 3.3.2 Bulk Operations
- [ ] **Multi-row selection**
  - Checkbox column for selecting rows
  - Shift+click for range selection
  - Ctrl/Cmd+click for individual selection
  - "Select All" checkbox

- [ ] **Bulk field update**
  - Select multiple rows
  - Choose field to update from dropdown
  - Enter new value
  - Apply to all selected rows

- [ ] **Fill down functionality**
  - Select cell with value
  - Drag down or click "Fill Down" button
  - Copy value to cells below

- [ ] **Copy-paste support**
  - Copy cells from external spreadsheet (Excel/Google Sheets)
  - Paste into grid
  - Parse clipboard data (TSV format)
  - Map pasted columns to grid columns

- [ ] **Find & Replace**
  - Open find/replace dialog
  - Enter search term and replacement
  - Choose field(s) to search
  - Preview matches before replacing
  - Apply to selected rows or all rows

#### 3.3.3 Filtering & Search
- [ ] **Add filter toolbar**
  - Search by title (text input)
  - Filter by publisher (dropdown)
  - Filter by collection (dropdown)
  - Filter by language (multi-select)
  - Filter by access level (multi-select)
  - Filter by active status (checkbox)
  - Date range for publication year

- [ ] **Apply filters**
  - Fetch filtered results from API
  - Update grid display
  - Show active filter badges
  - Clear filters button

#### 3.3.4 Relationship Editing
- [ ] **Publisher field**
  - Autocomplete dropdown
  - Search existing publishers
  - Create new publisher inline
  - Display publisher name, edit as dropdown

- [ ] **Collection field**
  - Autocomplete dropdown
  - Search existing collections
  - Create new collection inline

- [ ] **Languages field**
  - Multi-select dropdown
  - Tag display in cell
  - Add/remove languages

- [ ] **Creators field**
  - Multi-select dropdown with roles
  - Display as tags with role badges
  - Add/remove creators

### 3.4 UI/UX Enhancements
- [ ] **Keyboard navigation**
  - Tab to move between cells
  - Enter to edit cell
  - Escape to cancel edit
  - Arrow keys to navigate

- [ ] **Visual feedback**
  - Highlight edited cells (yellow background)
  - Show loading spinner during save
  - Show success toast after save
  - Show error messages clearly

- [ ] **Undo/Redo**
  - Track edit history
  - Ctrl+Z to undo
  - Ctrl+Y to redo
  - Show undo stack in UI

- [ ] **Row actions**
  - View full book details (link to edit page)
  - Duplicate row
  - Delete row
  - Open in new tab

---

## 🔗 **PHASE 4: INTEGRATION & WORKFLOW** (Week 4)

### 4.1 Connect with Existing Filament Resources
- [ ] **Add link from Books list to Bulk Edit**
  - Add "Bulk Edit" button to BookResource header
  - Pre-filter bulk editor with current list filters

- [ ] **Add link from Bulk Edit to single Book edit**
  - Add action column with "Edit" link
  - Open in modal or new tab

### 4.2 Book Duplication Feature
- [ ] **Add "Duplicate Book" action**
  - Available in Books list table
  - Available in Bulk Edit grid
  - Copy all fields except unique identifiers (internal_id, slug)
  - Append "(Copy)" to title
  - Copy relationships (creators, languages, classifications)
  - Exclude files (thumbnails, PDFs)

- [ ] **Create "Use as Template" feature**
  - Select a book as template
  - Create new book with pre-filled common fields
  - Only edit differences (title, publication_year, etc.)
  - Save and create another (workflow for series)

### 4.3 CSV Import Workflow
- [ ] **Create CSV import page**
  - Upload CSV file
  - Preview first 10 rows
  - Map columns to fields
  - Validate data
  - Show import summary (X successful, Y errors)
  - Apply import

- [ ] **Handle initial 1000+ book upload**
  - Test with large dataset
  - Implement chunked processing (100 books per batch)
  - Show progress bar
  - Allow cancellation

---

## 🧪 **PHASE 5: TESTING & VALIDATION** (Week 5)

### 5.1 Unit Tests
- [ ] **Test BulkEditingService**
  - Test `getBooksForBulkEdit()` with various filters
  - Test `validateBulkChanges()` with valid and invalid data
  - Test `applyBulkChanges()` with transaction rollback
  - Test `bulkUpdate()` across multiple books
  - Test `findAndReplace()` with edge cases

- [ ] **Test API endpoints**
  - Test authentication/authorization
  - Test validation responses
  - Test error handling

### 5.2 Integration Tests
- [ ] **Test full bulk editing workflow**
  - Load books in grid
  - Edit multiple cells
  - Save changes
  - Verify database updates

- [ ] **Test CSV import/export**
  - Export books to CSV
  - Modify CSV
  - Re-import CSV
  - Verify changes in database

- [ ] **Test book duplication**
  - Duplicate book
  - Verify all fields copied correctly
  - Verify relationships copied

### 5.3 Performance Tests
- [ ] **Test with 100+ books in grid**
  - Measure load time
  - Measure save time
  - Ensure responsive UI

- [ ] **Test bulk update of 100+ books**
  - Measure update time
  - Ensure no timeouts
  - Test transaction handling

### 5.4 User Acceptance Testing
- [ ] **Test with real use cases**
  - Add 10 books from same series
  - Correct misspelled publisher name across 50 books
  - Update collection name for 30 books
  - Bulk change access level for 100 books

- [ ] **Measure time improvements**
  - Compare time vs. one-by-one editing
  - Confirm "1 minute like spreadsheet" goal achieved

---

## 📚 **PHASE 6: DOCUMENTATION** (Week 5)

### 6.1 User Documentation
- [ ] **Create user guide for bulk editing**
  - How to access bulk editor
  - How to edit cells
  - How to use bulk operations (fill down, find/replace)
  - How to copy-paste from Excel
  - How to use filters
  - How to duplicate books
  - How to use as template

- [ ] **Create CSV import guide**
  - CSV format requirements
  - How to export books
  - How to edit and re-import
  - Field mapping instructions

### 6.2 Developer Documentation
- [ ] **Document API endpoints**
  - Request/response formats
  - Authentication requirements
  - Error codes

- [ ] **Document BulkEditingService**
  - Method signatures
  - Usage examples

- [ ] **Add code comments**
  - Explain complex logic
  - Document edge cases handled

---

## ⚙️ **PHASE 7: DEPLOYMENT & MONITORING** (Week 6)

### 7.1 Deployment
- [ ] **Prepare staging environment**
  - Deploy bulk editing feature
  - Test with production-like data

- [ ] **Run performance benchmarks**
  - Load 1000+ books
  - Measure response times
  - Optimize if needed

- [ ] **Deploy to production**
  - Merge feature branch
  - Run migrations if needed
  - Update documentation

### 7.2 Monitoring
- [ ] **Add logging**
  - Log bulk update operations
  - Log CSV imports
  - Log errors and exceptions

- [ ] **Track performance metrics**
  - Monitor API response times
  - Monitor database query times
  - Set up alerts for slow queries

### 7.3 Feedback & Iteration
- [ ] **Gather user feedback**
  - Survey admins using bulk editor
  - Identify pain points
  - Collect feature requests

- [ ] **Plan improvements**
  - Prioritize feedback
  - Schedule improvements for next iteration

---

## 🎯 **SUCCESS METRICS**

### Quantitative Metrics
- [ ] **Time to add 10 similar books**: < 2 minutes (vs. 10+ minutes currently)
- [ ] **Time to bulk update 100 books**: < 30 seconds
- [ ] **CSV import of 1000 books**: < 5 minutes
- [ ] **Grid load time (100 books)**: < 3 seconds
- [ ] **Bulk save time (100 books)**: < 10 seconds

### Qualitative Metrics
- [ ] Admin users find bulk editor intuitive
- [ ] Reduction in data entry errors
- [ ] Increased productivity for content management
- [ ] Positive feedback from client

---

## 📊 **PROGRESS TRACKING**

### Overall Completion: `0 / 150+` tasks

#### Phase 1 (Research & Planning): `0 / 18` ⏳
#### Phase 2 (Backend): `0 / 25` ⏳
#### Phase 3 (Frontend): `0 / 45` ⏳
#### Phase 4 (Integration): `0 / 12` ⏳
#### Phase 5 (Testing): `0 / 18` ⏳
#### Phase 6 (Documentation): `0 / 8` ⏳
#### Phase 7 (Deployment): `0 / 10` ⏳

---

## 🚀 **QUICK START - FIRST DAY TASKS**

### Priority 1: Make Technology Decision
1. Review grid library options (Handsontable, AG-Grid, Tabulator, Custom)
2. Create proof-of-concept with top 2 choices
3. Evaluate based on:
   - Licensing cost
   - Ease of integration with Livewire
   - Performance with 100+ rows
   - Spreadsheet-like UX
4. Document decision

### Priority 2: Define Scope
1. Review Book model and identify editable fields
2. Prioritize fields for Phase 1 (core fields only)
3. Document which fields are excluded and why

### Priority 3: Create Skeleton
1. Create BulkEditingService class
2. Create BulkEditingController with empty endpoints
3. Create BulkEditBooks Filament page with placeholder content
4. Add navigation link in Filament admin

---

## 🔄 **ALTERNATIVE APPROACHES**

### Approach A: Filament-Native Solution
- Use Filament's table with inline editing
- Use Filament's bulk actions
- **Pros**: Consistent with existing admin UI, less custom code
- **Cons**: May not feel as "spreadsheet-like", limited bulk operations

### Approach B: Full Spreadsheet Library (Handsontable/AG-Grid)
- Integrate commercial spreadsheet component
- Full Excel-like experience
- **Pros**: Best UX, familiar to users, extensive features
- **Cons**: License cost, more complex integration

### Approach C: Custom Alpine.js Grid
- Build custom editable table with Alpine.js
- Integrate with Livewire
- **Pros**: Full control, no licensing, matches tech stack
- **Cons**: More development time, need to implement all features

### Approach D: Hybrid (Filament + Enhanced Editing)
- Use Filament table as base
- Add custom JavaScript for bulk operations
- Use modals for complex editing
- **Pros**: Balance between consistency and functionality
- **Cons**: May feel disjointed

### **Recommendation**: Start with Approach B or C prototype, evaluate, then decide

---

## 📝 **NOTES & CONSIDERATIONS**

### Technical Constraints
- Laravel 12.x + Livewire 3.x stack
- FilamentPHP 3.x admin panel
- Must handle 1000+ books efficiently
- Mobile responsiveness may be limited (bulk editing is desktop-focused)

### Edge Cases to Handle
- Concurrent editing by multiple admins
- Very long text fields (descriptions)
- Invalid relationship IDs
- Duplicate book detection
- Unique constraint violations (slug, internal_id)

### Future Enhancements (Post-MVP)
- Column reordering and hiding
- Custom views (save filter + column configurations)
- Bulk edit history (audit log)
- Excel-style formulas for calculated fields
- Collaborative editing (see who else is editing)
- Mobile-optimized view
- Keyboard shortcuts reference
- Export to Excel with formatting
- Import from Google Sheets directly

---

**Last Updated**: [Current Date]
**Status**: Ready to Start
**Estimated Completion**: 6 weeks
**Next Step**: Phase 1 - Research & Technology Decision
