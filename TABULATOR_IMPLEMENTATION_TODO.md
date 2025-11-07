# Tabulator Bulk Editor - Complete Implementation Guide

**Technology**: Tabulator 6.3 (Open Source, MIT License)
**Timeline**: 3 weeks
**Status**: Ready to implement
**Documentation**: https://tabulator.info/docs/6.3

---

## 📋 TABLE OF CONTENTS

1. [Phase 1: Setup & Installation](#phase-1-setup--installation-days-1-2)
2. [Phase 2: Basic Table & Data Loading](#phase-2-basic-table--data-loading-days-3-4)
3. [Phase 3: Column Editors Configuration](#phase-3-column-editors-configuration-days-5-7)
4. [Phase 4: Data Validation](#phase-4-data-validation-days-8-9)
5. [Phase 5: Edit Events & Tracking](#phase-5-edit-events--tracking-day-10)
6. [Phase 6: Range Selection & Clipboard](#phase-6-range-selection--clipboard-days-11-12)
7. [Phase 7: Bulk Operations](#phase-7-bulk-operations-days-13-14)
8. [Phase 8: Save & Sync with Backend](#phase-8-save--sync-with-backend-days-15-16)
9. [Phase 9: Export/Import](#phase-9-exportimport-day-17)
10. [Phase 10: UI Polish & UX](#phase-10-ui-polish--ux-days-18-19)
11. [Phase 11: Testing](#phase-11-testing-days-20-21)

---

## 🎯 OVERVIEW

### Success Criteria
- ✅ Add 10 similar books in < 2 minutes
- ✅ Bulk update 100+ books in < 30 seconds
- ✅ Copy-paste from Excel/Google Sheets
- ✅ Inline editing for all editable fields
- ✅ Real-time validation with visual feedback
- ✅ Export to CSV/Excel for external editing

### Key Features to Implement
- Inline cell editing (text, dropdowns, multi-select, dates, toggles)
- Range selection (select multiple cells like Excel)
- Copy/paste (Ctrl+C, Ctrl+V from Excel)
- Clipboard integration (paste TSV data)
- Edit tracking (know which cells changed)
- Batch validation (validate all changes before save)
- Bulk operations (fill down, find/replace)
- Data export (CSV, Excel)
- Keyboard navigation (Tab, Enter, Arrow keys)

---

## 📦 PHASE 1: SETUP & INSTALLATION (Days 1-2) ✅ **COMPLETED**

### 1.1 Install Tabulator Package
- [x] **Install via NPM** ✅
  ```bash
  npm install tabulator-tables --save
  ```
  - Verify package.json includes: `"tabulator-tables": "^6.3.1"`

- [x] **Import Tabulator CSS** (add to resources/css/app.css or layout) ✅
  ```css
  @import 'tabulator-tables/dist/css/tabulator.min.css';
  ```
  OR use CDN for quick testing:
  ```html
  <link href="https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.min.css" rel="stylesheet">
  ```

- [x] **Import Tabulator JS** (add to resources/js/app.js) ✅
  ```javascript
  import { TabulatorFull as Tabulator } from 'tabulator-tables';
  ```
  OR use CDN:
  ```html
  <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>
  ```

- [x] **Build assets** ✅
  ```bash
  npm run build
  # or for development
  npm run dev
  ```

### 1.2 Create Filament Page
- [x] **Create BulkEditBooks Filament page** ✅
  ```bash
  php artisan make:filament-page BulkEditBooks
  ```
  - File location: `app/Filament/Pages/BulkEditBooks.php`

- [x] **Configure navigation** ✅
  ```php
  protected static ?string $navigationIcon = 'heroicon-o-table-cells';
  protected static ?string $navigationGroup = 'Library';
  protected static ?int $navigationSort = 15;
  protected static ?string $title = 'Bulk Edit Books';
  ```

- [x] **Add to navigation** (verify it appears in sidebar) ✅

### 1.3 Create Page Blade View
- [x] **Create blade file** ✅
  - Location: `resources/views/filament/pages/bulk-edit-books.blade.php`

- [x] **Basic HTML structure** ✅
  ```blade
  <x-filament-panels::page>
      <div class="space-y-4">
          {{-- Toolbar --}}
          <div class="flex items-center justify-between">
              <div class="flex gap-2">
                  {{-- Filters will go here --}}
              </div>
              <div class="flex gap-2">
                  {{-- Actions: Save, Export, Import --}}
              </div>
          </div>

          {{-- Tabulator Container --}}
          <div id="bulk-edit-table" class="bg-white dark:bg-gray-800 rounded-lg shadow"></div>
      </div>

      @push('scripts')
          <script>
              // Tabulator initialization will go here
          </script>
      @endpush
  </x-filament-panels::page>
  ```

### 1.4 Test Basic Setup
- [x] **Navigate to /admin/bulk-edit-books** ✅
- [x] **Verify page loads without errors** ✅
- [x] **Check browser console for any import errors** ✅
- [x] **Verify Tabulator CSS/JS are loaded** (inspect Network tab) ✅

**Deliverable**: Empty Filament page with Tabulator assets loaded ✅
**Time estimate**: 2 days

---

## 📊 PHASE 2: BASIC TABLE & DATA LOADING (Days 3-4) ✅ **COMPLETED**

### 2.1 Create API Endpoint for Bulk Data
- [x] **Create controller** ✅
  ```bash
  php artisan make:controller Admin/BulkEditingController
  ```

- [x] **Implement `index()` method** ✅
  ```php
  public function index(Request $request) {
      $query = Book::with(['publisher', 'collection', 'languages', 'creators']);

      // Apply filters if provided
      if ($request->has('publisher_id')) {
          $query->where('publisher_id', $request->publisher_id);
      }

      // Pagination
      $perPage = $request->get('per_page', 100);
      $page = $request->get('page', 1);

      $books = $query->paginate($perPage);

      return response()->json([
          'data' => $books->items(),
          'last_page' => $books->lastPage(),
          'total' => $books->total(),
      ]);
  }
  ```

- [x] **Add route** (routes/api.php) ✅
  ```php
  Route::middleware(['auth:sanctum', 'admin'])->group(function () {
      Route::get('/admin/bulk-editing/books', [BulkEditingController::class, 'index']);
  });
  ```

- [x] **Test API endpoint** (Postman/Insomnia) ✅
  - GET `/api/admin/bulk-editing/books`
  - Verify returns JSON with books data

### 2.2 Initialize Tabulator Table
- [x] **Add Tabulator initialization script** ✅
  ```javascript
  let table = new Tabulator("#bulk-edit-table", {
      height: "600px",
      layout: "fitColumns",
      placeholder: "No Books Available",
      pagination: true,
      paginationMode: "remote",
      ajaxURL: "/api/admin/bulk-editing/books",
      ajaxConfig: {
          method: "GET",
          headers: {
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
              "Authorization": "Bearer " + localStorage.getItem('auth_token'),
          }
      },
      columns: [
          {title: "ID", field: "id", width: 70},
          {title: "Title", field: "title", width: 300},
          {title: "Publisher", field: "publisher.name", width: 200},
          {title: "Year", field: "publication_year", width: 100},
      ],
  });
  ```

- [x] **Test table renders with data** ✅
  - Verify books load from API
  - Check pagination works
  - Verify columns display correctly

### 2.3 Configure Remote Pagination
- [x] **Set up pagination parameters** ✅
  ```javascript
  paginationSize: 50, // Rows per page
  paginationSizeSelector: [25, 50, 100, 200], // Dropdown options
  ajaxURLGenerator: function(url, config, params) {
      // Map Tabulator pagination params to Laravel
      url += "?page=" + params.page;
      url += "&per_page=" + params.size;
      return url;
  },
  ```

- [x] **Handle pagination response** ✅
  ```javascript
  ajaxResponse: function(url, params, response) {
      return {
          last_page: response.last_page,
          data: response.data,
      };
  },
  ```

- [x] **Test pagination** ✅
  - Change page numbers
  - Change rows per page
  - Verify API calls update correctly

### 2.4 Add Loading Indicator
- [x] **Enable progressive loading** ✅
  ```javascript
  progressiveLoad: "scroll", // Load more on scroll
  ajaxProgressiveLoadScrollMargin: 300, // Trigger 300px before bottom
  ```

- [x] **Add loading message** ✅
  ```javascript
  ajaxLoaderLoading: "<div class='text-center py-4'>Loading books...</div>",
  ajaxLoaderError: "<div class='text-center py-4 text-red-500'>Error loading data</div>",
  ```

**Deliverable**: Working table with data loading from API, pagination ✅
**Time estimate**: 2 days

---

## ✏️ PHASE 3: COLUMN EDITORS CONFIGURATION (Days 5-7) ✅ **COMPLETED**

### 3.1 Text Input Editors

#### 3.1.1 Title Field (Input Editor)
- [x] **Configure title column** ✅
  ```javascript
  {
      title: "Title",
      field: "title",
      width: 300,
      editor: "input",
      editorParams: {
          selectContents: true, // Select text on focus
          elementAttributes: {
              maxlength: "500",
          },
      },
      validator: ["required", "minLength:3", "maxLength:500"],
  }
  ```

- [x] **Test editing** ✅
  - Click cell to edit
  - Verify text selection on focus
  - Test validation (empty, too short, too long)

#### 3.1.2 Subtitle Field (Input Editor)
- [x] **Configure subtitle column** ✅
  ```javascript
  {
      title: "Subtitle",
      field: "subtitle",
      width: 250,
      editor: "input",
      editorParams: {
          selectContents: true,
          elementAttributes: {
              maxlength: "500",
          },
      },
      validator: "maxLength:500",
  }
  ```

#### 3.1.3 Translated Title (Input Editor)
- [x] **Configure translated_title column** ✅
  ```javascript
  {
      title: "Translated Title",
      field: "translated_title",
      width: 250,
      editor: "input",
      editorParams: {
          selectContents: true,
      },
      validator: "maxLength:500",
  }
  ```

#### 3.1.4 Publication Year (Input Editor with Number Validation)
- [x] **Configure publication_year column** ✅
  ```javascript
  {
      title: "Year",
      field: "publication_year",
      width: 100,
      editor: "input",
      editorParams: {
          elementAttributes: {
              type: "number",
              min: "1900",
              max: new Date().getFullYear().toString(),
          },
      },
      validator: ["integer", "min:1900", "max:" + new Date().getFullYear()],
  }
  ```

- [x] **Test validation** ✅
  - Try entering text (should fail)
  - Try year < 1900 (should fail)
  - Try year > current year (should fail)

#### 3.1.5 Pages (Input Editor with Number Validation)
- [x] **Configure pages column** ✅
  ```javascript
  {
      title: "Pages",
      field: "pages",
      width: 80,
      editor: "input",
      editorParams: {
          elementAttributes: {
              type: "number",
              min: "1",
          },
      },
      validator: ["integer", "min:1"],
  }
  ```

### 3.2 Textarea Editors

#### 3.2.1 Description Field (Textarea Editor)
- [x] **Configure description column** ✅
  ```javascript
  {
      title: "Description",
      field: "description",
      width: 300,
      editor: "textarea",
      editorParams: {
          elementAttributes: {
              rows: "4",
          },
          verticalNavigation: "editor", // Allow arrow keys in textarea
      },
      formatter: "textarea", // Display with line breaks
  }
  ```

- [x] **Test textarea** ✅
  - Enter multi-line text
  - Verify line breaks preserved
  - Test arrow key navigation (should stay in editor)

### 3.3 List/Dropdown Editors

#### 3.3.1 Publisher (List Editor with Search)
- [x] **Load publishers list** (API call) ✅
  ```javascript
  let publishers = [];
  fetch('/api/admin/publishers')
      .then(r => r.json())
      .then(data => {
          publishers = data.map(p => ({label: p.name, value: p.id}));
      });
  ```

- [x] **Configure publisher column** ✅
  ```javascript
  {
      title: "Publisher",
      field: "publisher_id",
      width: 200,
      editor: "list",
      editorParams: {
          values: publishers,
          autocomplete: true, // Enable search/filter
          freetext: false, // Only allow selection from list
          allowEmpty: true, // Allow clearing value
          listOnEmpty: true, // Show all options when empty
      },
      formatter: function(cell) {
          let pub = publishers.find(p => p.value === cell.getValue());
          return pub ? pub.label : "";
      },
  }
  ```

- [x] **Test dropdown** ✅
  - Click to open dropdown
  - Type to search/filter
  - Select publisher
  - Verify formatter displays name (not ID)

#### 3.3.2 Collection (List Editor with Search)
- [x] **Load collections list** ✅
  ```javascript
  let collections = [];
  fetch('/api/admin/collections')
      .then(r => r.json())
      .then(data => {
          collections = data.map(c => ({label: c.name, value: c.id}));
      });
  ```

- [x] **Configure collection column** ✅
  ```javascript
  {
      title: "Collection",
      field: "collection_id",
      width: 200,
      editor: "list",
      editorParams: {
          values: collections,
          autocomplete: true,
          allowEmpty: true,
      },
      formatter: function(cell) {
          let col = collections.find(c => c.value === cell.getValue());
          return col ? col.label : "";
      },
  }
  ```

#### 3.3.3 Access Level (List Editor - Simple Dropdown)
- [x] **Configure access_level column** ✅
  ```javascript
  {
      title: "Access Level",
      field: "access_level",
      width: 150,
      editor: "list",
      editorParams: {
          values: [
              {label: "Full Access", value: "full"},
              {label: "Limited Access", value: "limited"},
              {label: "Unavailable", value: "unavailable"},
          ],
      },
      formatter: function(cell) {
          const badges = {
              full: '<span class="badge badge-success">Full</span>',
              limited: '<span class="badge badge-warning">Limited</span>',
              unavailable: '<span class="badge badge-danger">Unavailable</span>',
          };
          return badges[cell.getValue()] || cell.getValue();
      },
  }
  ```

- [x] **Style badges** (add CSS) ✅
  ```css
  .badge {
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
  }
  .badge-success { background: #10b981; color: white; }
  .badge-warning { background: #f59e0b; color: white; }
  .badge-danger { background: #ef4444; color: white; }
  ```

#### 3.3.4 Physical Type (List Editor)
- [x] **Configure physical_type column** ✅
  ```javascript
  {
      title: "Physical Type",
      field: "physical_type",
      width: 150,
      editor: "list",
      editorParams: {
          values: [
              {label: "Book", value: "book"},
              {label: "Journal", value: "journal"},
              {label: "Magazine", value: "magazine"},
              {label: "Workbook", value: "workbook"},
              {label: "Poster", value: "poster"},
              {label: "Other", value: "other"},
          ],
      },
  }
  ```

### 3.4 Multi-Select Editors (Languages, Creators)

#### 3.4.1 Languages (List Editor with Multi-Select)
- [x] **Load languages list** ✅
  ```javascript
  let languages = [];
  fetch('/api/admin/languages')
      .then(r => r.json())
      .then(data => {
          languages = data.map(l => ({label: l.name, value: l.id}));
      });
  ```

- [x] **Configure languages column** ✅
  ```javascript
  {
      title: "Languages",
      field: "language_ids",
      width: 200,
      editor: "list",
      editorParams: {
          values: languages,
          multiselect: true, // Enable multi-select
          autocomplete: true,
      },
      formatter: function(cell) {
          const ids = cell.getValue() || [];
          const names = ids.map(id => {
              const lang = languages.find(l => l.value === id);
              return lang ? lang.label : "";
          }).filter(n => n);
          return names.join(", ");
      },
  }
  ```

- [x] **Test multi-select** ✅
  - Select multiple languages
  - Remove selected language
  - Verify formatter displays comma-separated names

#### 3.4.2 Creators/Authors (List Editor with Multi-Select)
- [x] **Load creators list** ✅
  ```javascript
  let creators = [];
  fetch('/api/admin/creators')
      .then(r => r.json())
      .then(data => {
          creators = data.map(c => ({label: c.name, value: c.id}));
      });
  ```

- [x] **Configure creators column** ✅
  ```javascript
  {
      title: "Authors/Creators",
      field: "creator_ids",
      width: 250,
      editor: "list",
      editorParams: {
          values: creators,
          multiselect: true,
          autocomplete: true,
      },
      formatter: function(cell) {
          const ids = cell.getValue() || [];
          const names = ids.map(id => {
              const creator = creators.find(c => c.value === id);
              return creator ? creator.label : "";
          }).filter(n => n);
          return names.join(", ");
      },
  }
  ```

### 3.5 Boolean/Toggle Editors

#### 3.5.1 Is Featured (Tickbox)
- [x] **Configure is_featured column** ✅
  ```javascript
  {
      title: "Featured",
      field: "is_featured",
      width: 100,
      hozAlign: "center",
      editor: "tickCross",
      formatter: "tickCross",
  }
  ```

- [x] **Test toggle** ✅
  - Click to toggle on/off
  - Verify checkmark/cross displays

#### 3.5.2 Is Active (Tickbox)
- [x] **Configure is_active column** ✅
  ```javascript
  {
      title: "Active",
      field: "is_active",
      width: 100,
      hozAlign: "center",
      editor: "tickCross",
      formatter: "tickCross",
  }
  ```

### 3.6 Date Editors (Optional)

#### 3.6.1 Created At / Updated At (Date Editor - Read Only)
- [x] **Configure created_at column** (if needed) - ⏭️ SKIPPED (Optional)
  ```javascript
  {
      title: "Created",
      field: "created_at",
      width: 150,
      formatter: "datetime",
      formatterParams: {
          inputFormat: "iso",
          outputFormat: "MM/dd/yyyy",
      },
      // No editor - read only
  }
  ```

### 3.7 Custom Editors (Advanced)

#### 3.7.1 Create Custom Editor Function (if needed)
- [x] **Define custom editor** - ⏭️ SKIPPED (Advanced/Optional)
  ```javascript
  var customEditor = function(cell, onRendered, success, cancel, editorParams) {
      // Create input element
      var input = document.createElement("input");
      input.type = "text";
      input.value = cell.getValue();
      input.style.width = "100%";

      // On blur, save value
      input.addEventListener("blur", function() {
          success(input.value);
      });

      // On enter, save value
      input.addEventListener("keydown", function(e) {
          if (e.key === "Enter") {
              success(input.value);
          } else if (e.key === "Escape") {
              cancel();
          }
      });

      // Focus input after render
      onRendered(function() {
          input.focus();
          input.select();
      });

      return input;
  };
  ```

- [x] **Use custom editor** (example) - ⏭️ SKIPPED (Advanced/Optional)
  ```javascript
  {
      title: "Custom Field",
      field: "custom_field",
      editor: customEditor,
  }
  ```

**Deliverable**: All editable columns configured with appropriate editors ✅
**Time estimate**: 3 days

---

## ✅ PHASE 4: DATA VALIDATION (Days 8-9) ✅ **COMPLETED**

### 4.1 Built-in Validators

#### 4.1.1 Required Validator
- [x] **Apply to title column** ✅
  ```javascript
  validator: "required"
  ```

- [x] **Test**: Try to clear title (should show error) ✅

#### 4.1.2 String Length Validators
- [x] **Apply min/max length** ✅
  ```javascript
  validator: ["required", "minLength:3", "maxLength:500"]
  ```

- [x] **Test**: Enter 1-2 characters (should fail), 501 characters (should fail) ✅

#### 4.1.3 Numeric Validators
- [x] **Apply to publication_year** ✅
  ```javascript
  validator: ["integer", "min:1900", "max:2025"]
  ```

- [x] **Test**: Enter text (fail), negative (fail), 1800 (fail), 2030 (fail) ✅

#### 4.1.4 Unique Validator (Custom - Server Side)
- [x] **Create custom unique validator** - ⏭️ SKIPPED (Advanced/Optional server-side feature)
  ```javascript
  var uniqueValidator = function(cell, value, parameters) {
      return new Promise((resolve, reject) => {
          fetch('/api/admin/validate/unique', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
              },
              body: JSON.stringify({
                  field: parameters.field,
                  value: value,
                  exclude_id: cell.getRow().getData().id,
              }),
          })
          .then(r => r.json())
          .then(data => {
              if (data.unique) {
                  resolve(true);
              } else {
                  reject(`${parameters.field} must be unique`);
              }
          });
      });
  };
  ```

- [x] **Apply to internal_id** (example) - ⏭️ SKIPPED (Optional)
  ```javascript
  {
      title: "Internal ID",
      field: "internal_id",
      editor: "input",
      validator: [uniqueValidator, {field: "internal_id"}],
  }
  ```

### 4.2 Custom Validators

#### 4.2.1 Create Year Range Validator
- [x] **Define validator function** ✅
  ```javascript
  var yearRangeValidator = function(cell, value, parameters) {
      const currentYear = new Date().getFullYear();
      const minYear = parameters.min || 1900;
      const maxYear = parameters.max || currentYear;

      if (!value) return true; // Allow empty if not required

      const year = parseInt(value);
      if (isNaN(year)) {
          return `Must be a valid year`;
      }
      if (year < minYear || year > maxYear) {
          return `Year must be between ${minYear} and ${maxYear}`;
      }
      return true;
  };
  ```

- [x] **Apply to publication_year** ✅
  ```javascript
  validator: [yearRangeValidator, {min: 1900, max: new Date().getFullYear()}]
  ```

#### 4.2.2 Create Publisher Exists Validator
- [x] **Define validator** ✅
  ```javascript
  var publisherExistsValidator = function(cell, value, parameters) {
      if (!value) return true; // Allow empty
      const exists = publishers.some(p => p.value === value);
      return exists ? true : "Publisher does not exist";
  };
  ```

- [x] **Apply to publisher_id** ✅
  ```javascript
  validator: publisherExistsValidator
  ```

### 4.3 Validation Error Display

#### 4.3.1 Configure Validation Styling
- [x] **Add CSS for invalid cells** ✅
  ```css
  .tabulator-cell.tabulator-validation-fail {
      border: 2px solid #ef4444 !important;
      background-color: #fee2e2 !important;
  }

  .tabulator-cell.tabulator-validation-fail:hover::after {
      content: attr(data-validation-error);
      position: absolute;
      background: #ef4444;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      z-index: 1000;
      top: 100%;
      left: 0;
      white-space: nowrap;
  }
  ```

#### 4.3.2 Display Validation Error on Hover
- [x] **Add validation fail event** ✅
  ```javascript
  table.on("validationFailed", function(cell, value, validators) {
      // Add error message to cell
      cell.getElement().setAttribute('data-validation-error', validators[0].error);
  });
  ```

- [x] **Clear error on success** ✅
  ```javascript
  table.on("cellEdited", function(cell) {
      cell.getElement().removeAttribute('data-validation-error');
  });
  ```

### 4.4 Batch Validation (Pre-Save)

#### 4.4.1 Create Validation Function
- [x] **Define validateAllChanges()** ✅
  ```javascript
  function validateAllChanges() {
      const editedCells = table.getEditedCells();
      const errors = [];

      editedCells.forEach(cell => {
          const valid = cell.validate();
          if (valid !== true) {
              errors.push({
                  row: cell.getRow().getPosition(),
                  column: cell.getColumn().getDefinition().title,
                  error: valid,
              });
          }
      });

      if (errors.length > 0) {
          displayValidationErrors(errors);
          return false;
      }
      return true;
  }
  ```

- [x] **Display errors in modal/alert** ✅
  ```javascript
  function displayValidationErrors(errors) {
      let errorHtml = '<ul class="list-disc pl-5">';
      errors.forEach(err => {
          errorHtml += `<li>Row ${err.row}, ${err.column}: ${err.error}</li>`;
      });
      errorHtml += '</ul>';

      // Show in Filament notification or modal
      new FilamentNotification()
          .title('Validation Errors')
          .danger()
          .body(errorHtml)
          .send();
  }
  ```

- [x] **Call before save** ✅
  ```javascript
  document.getElementById('save-button').addEventListener('click', function() {
      if (validateAllChanges()) {
          saveChanges();
      }
  });
  ```

**Deliverable**: All fields validated with visual error feedback ✅
**Time estimate**: 2 days

---

## 📡 PHASE 5: EDIT EVENTS & TRACKING (Day 10) ✅ **COMPLETED**

### 5.1 Cell Edited Event

#### 5.1.1 Track Cell Edits
- [x] **Add cellEdited callback** ✅
  ```javascript
  table.on("cellEdited", function(cell) {
      console.log("Cell edited:", {
          row: cell.getRow().getData().id,
          field: cell.getField(),
          oldValue: cell.getOldValue(),
          newValue: cell.getValue(),
      });

      // Mark row as changed
      cell.getRow().getElement().classList.add('row-changed');
  });
  ```

- [x] **Add CSS for changed rows** ✅
  ```css
  .tabulator-row.row-changed {
      background-color: #fef3c7 !important; /* Light yellow */
  }
  ```

#### 5.1.2 Track Edit Count
- [x] **Add edit counter** ✅
  ```javascript
  let editCount = 0;

  table.on("cellEdited", function(cell) {
      editCount++;
      document.getElementById('edit-count').textContent = editCount + ' changes';
  });
  ```

- [x] **Add counter to UI** ✅
  ```html
  <div class="flex items-center gap-2">
      <span class="text-sm text-gray-600">Unsaved changes:</span>
      <span id="edit-count" class="font-bold text-orange-600">0 changes</span>
  </div>
  ```

### 5.2 Get Edited Cells

#### 5.2.1 Retrieve Edited Cells
- [x] **Add function to get edited cells** ✅
  ```javascript
  function getEditedData() {
      const editedCells = table.getEditedCells();
      const changes = {};

      editedCells.forEach(cell => {
          const bookId = cell.getRow().getData().id;
          const field = cell.getField();
          const value = cell.getValue();

          if (!changes[bookId]) {
              changes[bookId] = {id: bookId};
          }
          changes[bookId][field] = value;
      });

      return Object.values(changes);
  }
  ```

- [x] **Test function** ✅
  ```javascript
  console.log(getEditedData());
  // Example output:
  // [
  //   {id: 1, title: "New Title", publication_year: 2024},
  //   {id: 5, publisher_id: 3},
  // ]
  ```

### 5.3 Clear Edit History

#### 5.3.1 Clear Edited Flags
- [x] **Add function to clear edits** ✅
  ```javascript
  function clearEditHistory() {
      const editedCells = table.getEditedCells();
      editedCells.forEach(cell => {
          cell.clearEdited();
      });

      // Clear visual indicators
      table.getRows().forEach(row => {
          row.getElement().classList.remove('row-changed');
      });

      editCount = 0;
      document.getElementById('edit-count').textContent = '0 changes';
  }
  ```

- [x] **Call after successful save** ✅ (Ready for Phase 8)

### 5.4 Data Changed Callback

#### 5.4.1 Track Any Data Change
- [x] **Add dataChanged callback** (optional - fires on any change) ✅
  ```javascript
  table.on("dataChanged", function(data) {
      console.log("Table data changed, current data:", data);
  });
  ```

**Deliverable**: Edit tracking system with visual feedback ✅
**Time estimate**: 1 day

---

## 📋 PHASE 6: RANGE SELECTION & CLIPBOARD (Days 11-12)

### 6.1 Enable Range Selection Module

#### 6.1.1 Configure Range Selection
- [ ] **Enable range selection in Tabulator**
  ```javascript
  let table = new Tabulator("#bulk-edit-table", {
      // ... other config
      selectableRange: true, // Enable range selection
      selectableRangeMode: "click", // Mode: click, drag
  });
  ```

- [ ] **Test range selection**
  - Click cell
  - Hold Shift + click another cell
  - Verify range highlighted
  - Use arrow keys with Shift to expand range

### 6.2 Enable Clipboard Module

#### 6.2.1 Configure Clipboard
- [ ] **Enable clipboard in Tabulator**
  ```javascript
  clipboard: true,
  clipboardCopyRowRange: "range", // Copy selected range
  clipboardCopyConfig: {
      rowHeaders: false, // Don't include row numbers
      columnHeaders: false, // Don't include column headers
  },
  clipboardCopyStyled: false, // Plain text only
  clipboardPasteParser: "range", // Parse pasted data as range
  clipboardPasteAction: "range", // Paste into range
  ```

#### 6.2.2 Test Copy (Ctrl+C)
- [ ] **Select cells**
- [ ] **Press Ctrl+C** (or Cmd+C on Mac)
- [ ] **Paste into Excel/Google Sheets**
- [ ] **Verify data copied correctly** (tab-separated)

#### 6.2.3 Test Paste (Ctrl+V)
- [ ] **Copy cells from Excel** (e.g., 3 rows × 2 columns)
- [ ] **Select cell in Tabulator**
- [ ] **Press Ctrl+V**
- [ ] **Verify data pasted correctly**
- [ ] **Check paste-to-fill** (data duplicates if range larger than selection)

### 6.3 Custom Paste Handling

#### 6.3.1 Parse Pasted Data
- [ ] **Add custom paste parser** (if needed)
  ```javascript
  clipboardPasteParser: function(clipboard) {
      // Parse TSV data from clipboard
      const rows = clipboard.trim().split('\n');
      const data = rows.map(row => row.split('\t'));
      return data;
  }
  ```

#### 6.3.2 Handle Paste Action
- [ ] **Add custom paste action** (if needed)
  ```javascript
  clipboardPasteAction: function(rowData, parsedData) {
      // Custom logic for pasting data
      // rowData = current selected cells
      // parsedData = data from clipboard

      // Apply paste logic
      // ...
  }
  ```

### 6.4 Spreadsheet Mode (Full Excel-like Experience)

#### 6.4.1 Enable Spreadsheet Module
- [ ] **Enable spreadsheet module**
  ```javascript
  spreadsheet: true, // Enable spreadsheet mode
  spreadsheetRows: 50, // Initial rows
  spreadsheetColumns: 10, // Initial columns
  ```

- [ ] **Test spreadsheet features**
  - Range selection with Shift+Arrow keys
  - Copy with Ctrl+C
  - Paste with Ctrl+V
  - Fill down (paste duplicates if range larger)

**Note**: Spreadsheet mode may require additional configuration depending on use case.

**Deliverable**: Copy-paste from Excel working, range selection enabled
**Time estimate**: 2 days

---

## 🔧 PHASE 7: BULK OPERATIONS (Days 13-14)

### 7.1 Row Selection (Checkboxes)

#### 7.1.1 Add Selection Column
- [ ] **Add checkbox column**
  ```javascript
  {
      formatter: "rowSelection",
      titleFormatter: "rowSelection",
      hozAlign: "center",
      headerSort: false,
      width: 50,
      frozen: true, // Keep column fixed on scroll
  }
  ```

- [ ] **Enable row selection**
  ```javascript
  selectable: true, // Enable row selection
  selectableRollingSelection: false, // Click to toggle, not rolling selection
  ```

- [ ] **Test row selection**
  - Click checkbox to select row
  - Click header checkbox to select all
  - Verify selection state

### 7.2 Bulk Update Action

#### 7.2.1 Add Bulk Update Button
- [ ] **Add button to toolbar**
  ```html
  <button id="bulk-update-btn" type="button" class="btn btn-secondary">
      Bulk Update Selected
  </button>
  ```

#### 7.2.2 Create Bulk Update Modal
- [ ] **Create modal HTML**
  ```html
  <div id="bulk-update-modal" class="hidden">
      <div class="modal-content">
          <h3>Bulk Update</h3>
          <p><span id="selected-count">0</span> rows selected</p>

          <div class="form-group">
              <label>Field to Update:</label>
              <select id="bulk-field">
                  <option value="">-- Select Field --</option>
                  <option value="publisher_id">Publisher</option>
                  <option value="collection_id">Collection</option>
                  <option value="access_level">Access Level</option>
                  <option value="is_featured">Featured</option>
                  <option value="is_active">Active</option>
              </select>
          </div>

          <div class="form-group">
              <label>New Value:</label>
              <input type="text" id="bulk-value" />
              <!-- Or dropdown, depending on field -->
          </div>

          <div class="flex gap-2">
              <button id="apply-bulk-update" class="btn btn-primary">Apply</button>
              <button id="cancel-bulk-update" class="btn btn-secondary">Cancel</button>
          </div>
      </div>
  </div>
  ```

#### 7.2.3 Implement Bulk Update Logic
- [ ] **Open modal on button click**
  ```javascript
  document.getElementById('bulk-update-btn').addEventListener('click', function() {
      const selectedRows = table.getSelectedRows();
      if (selectedRows.length === 0) {
          alert('No rows selected');
          return;
      }

      document.getElementById('selected-count').textContent = selectedRows.length;
      document.getElementById('bulk-update-modal').classList.remove('hidden');
  });
  ```

- [ ] **Apply bulk update**
  ```javascript
  document.getElementById('apply-bulk-update').addEventListener('click', function() {
      const field = document.getElementById('bulk-field').value;
      const value = document.getElementById('bulk-value').value;

      if (!field) {
          alert('Please select a field');
          return;
      }

      const selectedRows = table.getSelectedRows();
      selectedRows.forEach(row => {
          row.update({[field]: value});
      });

      document.getElementById('bulk-update-modal').classList.add('hidden');
      alert(`Updated ${selectedRows.length} rows`);
  });
  ```

- [ ] **Test bulk update**
  - Select multiple rows
  - Choose field (e.g., Access Level)
  - Enter value (e.g., "full")
  - Apply
  - Verify all selected rows updated

### 7.3 Fill Down Functionality

#### 7.3.1 Add Fill Down Button
- [ ] **Add button to toolbar**
  ```html
  <button id="fill-down-btn" type="button" class="btn btn-secondary">
      Fill Down
  </button>
  ```

#### 7.3.2 Implement Fill Down Logic
- [ ] **Fill down from selected cell**
  ```javascript
  document.getElementById('fill-down-btn').addEventListener('click', function() {
      const selectedCells = table.getSelectedData("range");
      if (selectedCells.length === 0) {
          alert('No cells selected');
          return;
      }

      // Get first cell value
      const firstCell = selectedCells[0];
      const field = Object.keys(firstCell)[0]; // Assuming single column
      const value = firstCell[field];

      // Apply to all selected cells
      selectedCells.forEach(cell => {
          // Update cell
          table.getRow(cell._id).update({[field]: value});
      });
  });
  ```

- [ ] **Test fill down**
  - Enter value in cell
  - Select cell + cells below
  - Click "Fill Down"
  - Verify all cells get first cell's value

### 7.4 Find & Replace

#### 7.4.1 Add Find & Replace Button
- [ ] **Add button to toolbar**
  ```html
  <button id="find-replace-btn" type="button" class="btn btn-secondary">
      Find & Replace
  </button>
  ```

#### 7.4.2 Create Find & Replace Modal
- [ ] **Create modal HTML**
  ```html
  <div id="find-replace-modal" class="hidden">
      <div class="modal-content">
          <h3>Find & Replace</h3>

          <div class="form-group">
              <label>Field:</label>
              <select id="find-field">
                  <option value="title">Title</option>
                  <option value="publisher.name">Publisher</option>
                  <option value="description">Description</option>
              </select>
          </div>

          <div class="form-group">
              <label>Find:</label>
              <input type="text" id="find-text" placeholder="Text to find" />
          </div>

          <div class="form-group">
              <label>Replace with:</label>
              <input type="text" id="replace-text" placeholder="Replacement text" />
          </div>

          <div class="form-group">
              <label>
                  <input type="checkbox" id="match-case" />
                  Match case
              </label>
          </div>

          <div id="find-results"></div>

          <div class="flex gap-2">
              <button id="find-btn" class="btn btn-secondary">Find</button>
              <button id="replace-all-btn" class="btn btn-primary">Replace All</button>
              <button id="cancel-find-replace" class="btn btn-secondary">Cancel</button>
          </div>
      </div>
  </div>
  ```

#### 7.4.3 Implement Find Logic
- [ ] **Find matches**
  ```javascript
  document.getElementById('find-btn').addEventListener('click', function() {
      const field = document.getElementById('find-field').value;
      const findText = document.getElementById('find-text').value;
      const matchCase = document.getElementById('match-case').checked;

      const data = table.getData();
      const matches = [];

      data.forEach((row, index) => {
          let cellValue = row[field];
          if (!matchCase) {
              cellValue = cellValue.toLowerCase();
              findText = findText.toLowerCase();
          }

          if (cellValue.includes(findText)) {
              matches.push({row: index + 1, id: row.id, value: cellValue});
          }
      });

      document.getElementById('find-results').innerHTML =
          `Found ${matches.length} matches`;
  });
  ```

#### 7.4.4 Implement Replace Logic
- [ ] **Replace all matches**
  ```javascript
  document.getElementById('replace-all-btn').addEventListener('click', function() {
      const field = document.getElementById('find-field').value;
      const findText = document.getElementById('find-text').value;
      const replaceText = document.getElementById('replace-text').value;
      const matchCase = document.getElementById('match-case').checked;

      let count = 0;
      table.getRows().forEach(row => {
          let cellValue = row.getData()[field];
          let updated = cellValue;

          if (matchCase) {
              updated = cellValue.replaceAll(findText, replaceText);
          } else {
              const regex = new RegExp(findText, 'gi');
              updated = cellValue.replace(regex, replaceText);
          }

          if (updated !== cellValue) {
              row.update({[field]: updated});
              count++;
          }
      });

      alert(`Replaced ${count} instances`);
      document.getElementById('find-replace-modal').classList.add('hidden');
  });
  ```

- [ ] **Test find & replace**
  - Find text (e.g., "Micronsia" typo)
  - Replace with "Micronesia"
  - Verify all instances replaced

**Deliverable**: Bulk operations working (select, update, fill down, find/replace)
**Time estimate**: 2 days

---

## 💾 PHASE 8: SAVE & SYNC WITH BACKEND (Days 15-16)

### 8.1 Create Save Button

#### 8.1.1 Add Save Button to Toolbar
- [ ] **Add button HTML**
  ```html
  <button id="save-changes-btn" type="button" class="btn btn-primary">
      <span id="save-icon">💾</span>
      Save Changes (<span id="save-count">0</span>)
  </button>
  ```

- [ ] **Update save count** (link to edit tracking)
  ```javascript
  table.on("cellEdited", function() {
      const count = table.getEditedCells().length;
      document.getElementById('save-count').textContent = count;
  });
  ```

### 8.2 Create Bulk Update API Endpoint

#### 8.2.1 Backend Endpoint
- [ ] **Add method to BulkEditingController**
  ```php
  public function bulkUpdate(Request $request) {
      $changes = $request->input('changes'); // Array of book changes

      // Validate changes
      $validated = $request->validate([
          'changes' => 'required|array',
          'changes.*.id' => 'required|exists:books,id',
          'changes.*.title' => 'sometimes|string|max:500',
          'changes.*.publication_year' => 'sometimes|integer|min:1900|max:' . date('Y'),
          // ... other validations
      ]);

      DB::beginTransaction();
      try {
          foreach ($changes as $change) {
              $book = Book::find($change['id']);
              $book->update($change);
          }
          DB::commit();

          return response()->json([
              'success' => true,
              'message' => 'Updated ' . count($changes) . ' books',
          ]);
      } catch (\Exception $e) {
          DB::rollBack();
          return response()->json([
              'success' => false,
              'message' => $e->getMessage(),
          ], 500);
      }
  }
  ```

- [ ] **Add route**
  ```php
  Route::post('/admin/bulk-editing/books/update', [BulkEditingController::class, 'bulkUpdate']);
  ```

### 8.3 Send Changes to Backend

#### 8.3.1 Implement Save Function
- [ ] **Create save function**
  ```javascript
  async function saveChanges() {
      const changes = getEditedData();

      if (changes.length === 0) {
          alert('No changes to save');
          return;
      }

      // Show loading indicator
      document.getElementById('save-icon').textContent = '⏳';
      document.getElementById('save-changes-btn').disabled = true;

      try {
          const response = await fetch('/api/admin/bulk-editing/books/update', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              },
              body: JSON.stringify({changes}),
          });

          const data = await response.json();

          if (data.success) {
              // Clear edit history
              clearEditHistory();

              // Show success message
              new FilamentNotification()
                  .title('Saved')
                  .success()
                  .body(data.message)
                  .send();
          } else {
              throw new Error(data.message);
          }
      } catch (error) {
          // Show error message
          new FilamentNotification()
              .title('Error')
              .danger()
              .body('Failed to save: ' + error.message)
              .send();
      } finally {
          // Reset button
          document.getElementById('save-icon').textContent = '💾';
          document.getElementById('save-changes-btn').disabled = false;
      }
  }
  ```

- [ ] **Attach to button**
  ```javascript
  document.getElementById('save-changes-btn').addEventListener('click', saveChanges);
  ```

### 8.4 Handle Save Errors

#### 8.4.1 Display Validation Errors from Server
- [ ] **Parse server errors**
  ```javascript
  if (response.status === 422) {
      // Validation errors
      const errors = await response.json();
      displayServerValidationErrors(errors);
  }
  ```

- [ ] **Display errors**
  ```javascript
  function displayServerValidationErrors(errors) {
      let errorHtml = '<ul>';
      Object.keys(errors.errors).forEach(field => {
          errors.errors[field].forEach(error => {
              errorHtml += `<li>${field}: ${error}</li>`;
          });
      });
      errorHtml += '</ul>';

      new FilamentNotification()
          .title('Validation Failed')
          .danger()
          .body(errorHtml)
          .send();
  }
  ```

### 8.5 Auto-Save (Optional)

#### 8.5.1 Implement Debounced Auto-Save
- [ ] **Add auto-save toggle**
  ```html
  <label>
      <input type="checkbox" id="auto-save-toggle" />
      Auto-save (30s)
  </label>
  ```

- [ ] **Implement auto-save**
  ```javascript
  let autoSaveTimer = null;

  document.getElementById('auto-save-toggle').addEventListener('change', function() {
      if (this.checked) {
          startAutoSave();
      } else {
          stopAutoSave();
      }
  });

  function startAutoSave() {
      autoSaveTimer = setInterval(() => {
          const changes = getEditedData();
          if (changes.length > 0) {
              saveChanges();
          }
      }, 30000); // Every 30 seconds
  }

  function stopAutoSave() {
      if (autoSaveTimer) {
          clearInterval(autoSaveTimer);
      }
  }
  ```

**Deliverable**: Save functionality working, data syncs to database
**Time estimate**: 2 days

---

## 📤 PHASE 9: EXPORT/IMPORT (Day 17)

### 9.1 Export to CSV

#### 9.1.1 Add Export Button
- [ ] **Add button to toolbar**
  ```html
  <button id="export-csv-btn" type="button" class="btn btn-secondary">
      📥 Export CSV
  </button>
  ```

#### 9.1.2 Implement CSV Export
- [ ] **Use Tabulator's built-in export**
  ```javascript
  document.getElementById('export-csv-btn').addEventListener('click', function() {
      table.download("csv", "books_export.csv", {
          delimiter: ",",
          bom: true, // Add UTF-8 BOM for Excel compatibility
      });
  });
  ```

- [ ] **Test CSV export**
  - Click export button
  - Open CSV in Excel
  - Verify data exported correctly
  - Check special characters (Unicode) display correctly

### 9.2 Export to Excel (XLSX)

#### 9.2.1 Install SheetJS Library
- [ ] **Install xlsx package**
  ```bash
  npm install xlsx --save
  ```

- [ ] **Import in JavaScript**
  ```javascript
  import * as XLSX from 'xlsx';
  ```

#### 9.2.2 Add Export Excel Button
- [ ] **Add button**
  ```html
  <button id="export-excel-btn" type="button" class="btn btn-secondary">
      📊 Export Excel
  </button>
  ```

#### 9.2.3 Implement Excel Export
- [ ] **Use Tabulator's built-in export**
  ```javascript
  document.getElementById('export-excel-btn').addEventListener('click', function() {
      table.download("xlsx", "books_export.xlsx", {
          sheetName: "Books",
      });
  });
  ```

- [ ] **Test Excel export**
  - Click export button
  - Open XLSX in Excel
  - Verify formatting preserved
  - Check dropdowns work (if applicable)

### 9.3 Import CSV

#### 9.3.1 Add Import Button & File Input
- [ ] **Add button and file input**
  ```html
  <input type="file" id="csv-file-input" accept=".csv" class="hidden" />
  <button id="import-csv-btn" type="button" class="btn btn-secondary" onclick="document.getElementById('csv-file-input').click()">
      📤 Import CSV
  </button>
  ```

#### 9.3.2 Parse Uploaded CSV
- [ ] **Handle file upload**
  ```javascript
  document.getElementById('csv-file-input').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = function(event) {
          const csvData = event.target.result;
          parseAndImportCSV(csvData);
      };
      reader.readAsText(file);
  });
  ```

- [ ] **Parse CSV data**
  ```javascript
  function parseAndImportCSV(csvData) {
      const rows = csvData.split('\n');
      const headers = rows[0].split(',');

      const data = [];
      for (let i = 1; i < rows.length; i++) {
          const values = rows[i].split(',');
          const row = {};
          headers.forEach((header, index) => {
              row[header.trim()] = values[index]?.trim();
          });
          data.push(row);
      }

      // Show preview modal
      showImportPreview(data);
  }
  ```

#### 9.3.3 Import Preview & Confirmation
- [ ] **Create import preview modal**
  ```html
  <div id="import-preview-modal" class="hidden">
      <h3>Import Preview</h3>
      <p>Importing <span id="import-count">0</span> books</p>
      <div id="import-preview-table"></div>
      <button id="confirm-import-btn" class="btn btn-primary">Confirm Import</button>
      <button id="cancel-import-btn" class="btn btn-secondary">Cancel</button>
  </div>
  ```

- [ ] **Show preview**
  ```javascript
  function showImportPreview(data) {
      document.getElementById('import-count').textContent = data.length;

      // Display first 5 rows in preview table
      let previewHtml = '<table><thead><tr>';
      const headers = Object.keys(data[0]);
      headers.forEach(h => previewHtml += `<th>${h}</th>`);
      previewHtml += '</tr></thead><tbody>';

      data.slice(0, 5).forEach(row => {
          previewHtml += '<tr>';
          headers.forEach(h => previewHtml += `<td>${row[h]}</td>`);
          previewHtml += '</tr>';
      });
      previewHtml += '</tbody></table>';

      document.getElementById('import-preview-table').innerHTML = previewHtml;
      document.getElementById('import-preview-modal').classList.remove('hidden');
  }
  ```

#### 9.3.4 Confirm & Send to Backend
- [ ] **Implement import confirmation**
  ```javascript
  document.getElementById('confirm-import-btn').addEventListener('click', async function() {
      const importData = getCurrentImportData(); // Store this globally

      try {
          const response = await fetch('/api/admin/bulk-editing/books/import', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
              },
              body: JSON.stringify({books: importData}),
          });

          const result = await response.json();

          if (result.success) {
              alert(`Imported ${result.count} books`);
              table.setData(); // Reload table
          }
      } catch (error) {
          alert('Import failed: ' + error.message);
      }

      document.getElementById('import-preview-modal').classList.add('hidden');
  });
  ```

- [ ] **Backend import endpoint**
  ```php
  public function import(Request $request) {
      $books = $request->input('books');

      DB::beginTransaction();
      try {
          foreach ($books as $bookData) {
              Book::create($bookData);
          }
          DB::commit();

          return response()->json([
              'success' => true,
              'count' => count($books),
          ]);
      } catch (\Exception $e) {
          DB::rollBack();
          return response()->json([
              'success' => false,
              'message' => $e->getMessage(),
          ], 500);
      }
  }
  ```

**Deliverable**: CSV/Excel export working, CSV import with preview
**Time estimate**: 1 day

---

## 🎨 PHASE 10: UI POLISH & UX (Days 18-19)

### 10.1 Styling & Theme

#### 10.1.1 Customize Tabulator Theme
- [ ] **Override default styles**
  ```css
  /* Custom Tabulator theme for Filament */
  .tabulator {
      font-size: 14px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
  }

  .tabulator-header {
      background: #f9fafb;
      border-bottom: 2px solid #e5e7eb;
  }

  .tabulator-header .tabulator-col {
      background: #f9fafb;
      border-right: 1px solid #e5e7eb;
      font-weight: 600;
  }

  .tabulator-row {
      border-bottom: 1px solid #f3f4f6;
  }

  .tabulator-row:hover {
      background: #f9fafb;
  }

  .tabulator-row.tabulator-selected {
      background: #dbeafe !important;
  }

  .tabulator-cell {
      padding: 8px 12px;
  }

  .tabulator-cell.tabulator-editing {
      border: 2px solid #3b82f6;
      background: #ffffff;
  }
  ```

#### 10.1.2 Dark Mode Support (if using Filament dark mode)
- [ ] **Add dark mode styles**
  ```css
  .dark .tabulator {
      background: #1f2937;
      border-color: #374151;
  }

  .dark .tabulator-header {
      background: #111827;
      border-bottom-color: #374151;
  }

  .dark .tabulator-row {
      background: #1f2937;
      border-bottom-color: #374151;
  }

  .dark .tabulator-row:hover {
      background: #374151;
  }

  .dark .tabulator-cell {
      color: #f9fafb;
  }
  ```

### 10.2 Keyboard Navigation

#### 10.2.1 Configure Keyboard Shortcuts
- [ ] **Enable keybindings**
  ```javascript
  keybindings: {
      "navPrev": "shift + 9", // Navigate to previous cell (Tab)
      "navNext": 9, // Navigate to next cell (Tab)
      "navUp": 38, // Navigate up (Arrow Up)
      "navDown": 40, // Navigate down (Arrow Down)
      "scrollPageUp": 33, // Page Up
      "scrollPageDown": 34, // Page Down
      "scrollToStart": 36, // Home
      "scrollToEnd": 35, // End
      "undo": 90, // Ctrl+Z
      "redo": "ctrl + 89", // Ctrl+Y
      "copyToClipboard": "ctrl + 67", // Ctrl+C
      "copyToClipboard": "ctrl + 67", // Ctrl+V (handled by clipboard module)
  }
  ```

#### 10.2.2 Add Keyboard Shortcuts Help
- [ ] **Add help icon/button**
  ```html
  <button id="keyboard-help-btn" type="button" class="btn btn-secondary">
      ⌨️ Shortcuts
  </button>
  ```

- [ ] **Create help modal**
  ```html
  <div id="keyboard-help-modal" class="hidden">
      <h3>Keyboard Shortcuts</h3>
      <table>
          <tr><td>Tab</td><td>Next cell</td></tr>
          <tr><td>Shift + Tab</td><td>Previous cell</td></tr>
          <tr><td>Enter</td><td>Edit cell / Save & move down</td></tr>
          <tr><td>Escape</td><td>Cancel edit</td></tr>
          <tr><td>Arrow Keys</td><td>Navigate cells</td></tr>
          <tr><td>Ctrl + C</td><td>Copy selected cells</td></tr>
          <tr><td>Ctrl + V</td><td>Paste</td></tr>
          <tr><td>Ctrl + Z</td><td>Undo (if enabled)</td></tr>
          <tr><td>Ctrl + S</td><td>Save changes</td></tr>
      </table>
  </div>
  ```

#### 10.2.3 Implement Ctrl+S to Save
- [ ] **Add global keyboard listener**
  ```javascript
  document.addEventListener('keydown', function(e) {
      // Ctrl+S or Cmd+S
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
          e.preventDefault();
          saveChanges();
      }
  });
  ```

### 10.3 Filters & Search

#### 10.3.1 Add Filter Toolbar
- [ ] **Create filter inputs**
  ```html
  <div class="flex gap-2 items-end">
      <div class="form-group">
          <label>Search Title</label>
          <input type="text" id="filter-title" placeholder="Search..." />
      </div>

      <div class="form-group">
          <label>Publisher</label>
          <select id="filter-publisher">
              <option value="">All</option>
              <!-- Populated dynamically -->
          </select>
      </div>

      <div class="form-group">
          <label>Collection</label>
          <select id="filter-collection">
              <option value="">All</option>
          </select>
      </div>

      <div class="form-group">
          <label>Language</label>
          <select id="filter-language">
              <option value="">All</option>
          </select>
      </div>

      <div class="form-group">
          <label>Access Level</label>
          <select id="filter-access-level">
              <option value="">All</option>
              <option value="full">Full</option>
              <option value="limited">Limited</option>
              <option value="unavailable">Unavailable</option>
          </select>
      </div>

      <button id="apply-filters-btn" class="btn btn-primary">Filter</button>
      <button id="clear-filters-btn" class="btn btn-secondary">Clear</button>
  </div>
  ```

#### 10.3.2 Implement Filter Logic
- [ ] **Apply filters to table**
  ```javascript
  document.getElementById('apply-filters-btn').addEventListener('click', function() {
      const filters = {
          title: document.getElementById('filter-title').value,
          publisher_id: document.getElementById('filter-publisher').value,
          collection_id: document.getElementById('filter-collection').value,
          language_id: document.getElementById('filter-language').value,
          access_level: document.getElementById('filter-access-level').value,
      };

      // Update Ajax URL with filters
      table.setData('/api/admin/bulk-editing/books?' + new URLSearchParams(filters));
  });
  ```

- [ ] **Clear filters**
  ```javascript
  document.getElementById('clear-filters-btn').addEventListener('click', function() {
      document.getElementById('filter-title').value = '';
      document.getElementById('filter-publisher').value = '';
      document.getElementById('filter-collection').value = '';
      document.getElementById('filter-language').value = '';
      document.getElementById('filter-access-level').value = '';

      table.setData('/api/admin/bulk-editing/books');
  });
  ```

### 10.4 Performance Optimizations

#### 10.4.1 Enable Virtual DOM Rendering
- [ ] **Already enabled by default** (Tabulator uses virtualized scrolling)
- [ ] **Verify with large dataset** (test with 1000+ rows)

#### 10.4.2 Lazy Load Relationships
- [ ] **Optimize backend query**
  ```php
  $books = Book::with(['publisher:id,name', 'collection:id,name'])
      ->select('id', 'title', 'publisher_id', 'collection_id', 'publication_year', 'access_level', 'is_featured', 'is_active')
      ->paginate(50);
  ```

#### 10.4.3 Debounce Filter Inputs
- [ ] **Add debounce to search**
  ```javascript
  let filterTimeout;
  document.getElementById('filter-title').addEventListener('input', function() {
      clearTimeout(filterTimeout);
      filterTimeout = setTimeout(() => {
          applyFilters();
      }, 500); // Wait 500ms after user stops typing
  });
  ```

### 10.5 Mobile Responsiveness (Limited)

#### 10.5.1 Add Viewport Notice
- [ ] **Detect mobile and show notice**
  ```javascript
  if (window.innerWidth < 768) {
      alert('Bulk editing works best on desktop. Some features may be limited on mobile.');
  }
  ```

- [ ] **Hide some columns on mobile**
  ```javascript
  {
      title: "Description",
      field: "description",
      responsive: 0, // Hide on smallest screens
  }
  ```

**Deliverable**: Polished UI with filters, keyboard shortcuts, theme
**Time estimate**: 2 days

---

## ✅ PHASE 11: TESTING (Days 20-21)

### 11.1 Unit Tests (Backend)

#### 11.1.1 Test Bulk Update Endpoint
- [ ] **Create test**
  ```php
  public function test_bulk_update_books() {
      $user = User::factory()->admin()->create();
      $books = Book::factory()->count(3)->create();

      $response = $this->actingAs($user)->postJson('/api/admin/bulk-editing/books/update', [
          'changes' => [
              ['id' => $books[0]->id, 'title' => 'Updated Title 1'],
              ['id' => $books[1]->id, 'access_level' => 'full'],
          ],
      ]);

      $response->assertStatus(200);
      $this->assertEquals('Updated Title 1', $books[0]->fresh()->title);
      $this->assertEquals('full', $books[1]->fresh()->access_level);
  }
  ```

#### 11.1.2 Test Validation
- [ ] **Test invalid data**
  ```php
  public function test_bulk_update_validation_fails() {
      $user = User::factory()->admin()->create();

      $response = $this->actingAs($user)->postJson('/api/admin/bulk-editing/books/update', [
          'changes' => [
              ['id' => 999, 'title' => ''], // Non-existent ID, empty title
          ],
      ]);

      $response->assertStatus(422);
  }
  ```

### 11.2 Integration Tests

#### 11.2.1 Test Full Workflow
- [ ] **Manual test: Add 10 books**
  - Navigate to bulk editor
  - Select first row, duplicate 9 times
  - Edit titles, years, publishers
  - Save changes
  - **Goal**: Complete in < 2 minutes

- [ ] **Manual test: Bulk update 100 books**
  - Load 100 books
  - Select all
  - Bulk update access level to "full"
  - Save changes
  - **Goal**: Complete in < 30 seconds

- [ ] **Manual test: Copy-paste from Excel**
  - Copy 5 rows × 3 columns from Excel
  - Paste into Tabulator
  - Verify data matches
  - Save changes

- [ ] **Manual test: Fill down**
  - Enter publisher in first cell
  - Select 10 cells below
  - Fill down
  - Verify all cells get same publisher

- [ ] **Manual test: Find & replace**
  - Find "Micronsia" (typo)
  - Replace with "Micronesia"
  - Verify all instances replaced

### 11.3 Performance Tests

#### 11.3.1 Load Time Test
- [ ] **Test with 100 books**
  - Measure time to load table
  - **Goal**: < 3 seconds

- [ ] **Test with 500 books**
  - Measure time to load table
  - **Goal**: < 5 seconds

- [ ] **Test with 1000 books**
  - Measure time to load table
  - **Goal**: < 10 seconds

#### 11.3.2 Save Time Test
- [ ] **Test saving 50 changes**
  - Edit 50 cells
  - Measure time to save
  - **Goal**: < 10 seconds

- [ ] **Test saving 100 changes**
  - Edit 100 cells
  - Measure time to save
  - **Goal**: < 20 seconds

### 11.4 Cross-Browser Testing

#### 11.4.1 Test on Major Browsers
- [ ] **Chrome** (primary)
  - All features work
  - Clipboard copy/paste works

- [ ] **Firefox**
  - All features work
  - Clipboard copy/paste works

- [ ] **Safari** (Mac only)
  - All features work
  - Clipboard may require permissions

- [ ] **Edge**
  - All features work
  - Clipboard copy/paste works

### 11.5 User Acceptance Testing

#### 11.5.1 Get Client Feedback
- [ ] **Schedule demo session**
  - Walkthrough all features
  - Let client test workflows
  - Gather feedback

- [ ] **Create feedback document**
  - List any issues found
  - List feature requests
  - Prioritize fixes

#### 11.5.2 Fix Critical Issues
- [ ] **Address show-stopper bugs**
- [ ] **Implement critical feedback**
- [ ] **Re-test after fixes**

**Deliverable**: Fully tested bulk editor ready for production
**Time estimate**: 2 days

---

## 📚 ADDITIONAL RESOURCES

### Official Documentation
- **Tabulator Docs**: https://tabulator.info/docs/6.3
- **Editing**: https://tabulator.info/docs/6.3/edit
- **Column Setup**: https://tabulator.info/docs/6.3/columns
- **Clipboard**: https://tabulator.info/docs/6.3/clipboard
- **Range Selection**: https://tabulator.info/docs/6.3/range
- **Spreadsheet Mode**: https://tabulator.info/docs/6.3/spreadsheet
- **Callbacks**: https://tabulator.info/docs/6.3/callbacks

### Examples
- **Examples Page**: https://tabulator.info/examples/6.3
- **CodePen Demo**: https://codepen.io/daniel1596/pen/wvGBxrJ
- **JSFiddle Sandbox**: https://tabulator.info/community/jsfiddle

### Libraries
- **Tabulator**: https://www.npmjs.com/package/tabulator-tables
- **SheetJS (xlsx)**: https://www.npmjs.com/package/xlsx
- **Laravel Excel**: https://laravel-excel.com/

---

## ✅ COMPLETION CHECKLIST

### Must-Have Features
- [ ] Table loads data from API
- [ ] Inline editing for all fields
- [ ] Dropdowns for publishers, collections, access level
- [ ] Multi-select for languages and creators
- [ ] Boolean toggles for is_featured, is_active
- [ ] Cell-level validation with error display
- [ ] Edit tracking (visual indicators for changed cells)
- [ ] Save button sends changes to backend
- [ ] Row selection with checkboxes
- [ ] Bulk update (change field across selected rows)
- [ ] Copy-paste from Excel/Google Sheets
- [ ] CSV export
- [ ] Excel export
- [ ] Pagination (50-100 books per page)
- [ ] Filters (title, publisher, collection, language, access level)

### Nice-to-Have Features
- [ ] Fill down functionality
- [ ] Find & replace modal
- [ ] Keyboard shortcuts (Tab, Enter, Ctrl+S, Ctrl+C/V)
- [ ] Auto-save (optional)
- [ ] CSV import with preview
- [ ] Undo/redo (if Tabulator supports)
- [ ] Dark mode support
- [ ] Keyboard shortcuts help modal

### Performance Goals
- [ ] Load 100 books in < 3 seconds
- [ ] Save 50 changes in < 10 seconds
- [ ] Bulk update 100 books in < 30 seconds
- [ ] Add 10 similar books in < 2 minutes

### Browser Support
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

---

## 📊 PROGRESS TRACKING

### Overall Progress: `88 / 150+` tasks ✅ 59% complete

#### Phase 1 (Setup): `13 / 13` ✅ **COMPLETED**
#### Phase 2 (Data Loading): `14 / 14` ✅ **COMPLETED**
#### Phase 3 (Editors): `35 / 35` ✅ **COMPLETED** (including translated_title & description)
#### Phase 4 (Validation): `17 / 17` ✅ **COMPLETED**
#### Phase 5 (Events & Tracking): `9 / 9` ✅ **COMPLETED**
#### Phase 6 (Range/Clipboard): `0 / 10`
#### Phase 7 (Bulk Ops): `0 / 15`
#### Phase 8 (Save): `0 / 12`
#### Phase 9 (Export/Import): `0 / 14`
#### Phase 10 (UI Polish): `0 / 20`
#### Phase 11 (Testing): `0 / 18`

---

## 🚀 NEXT STEPS

1. **Review this TODO with team/client**
2. **Confirm timeline (3 weeks acceptable?)**
3. **Confirm must-have vs nice-to-have features**
4. **Start Phase 1: Install Tabulator and create page**
5. **Daily standups to track progress**
6. **Weekly demos to show progress**

---

**Document Status**: ✅ Complete
**Ready for**: Implementation
**Estimated Completion**: 21 days (3 weeks)
**Last Updated**: November 6, 2025
