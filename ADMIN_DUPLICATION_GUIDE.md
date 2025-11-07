# Book Duplication Guide for Admins

**Version**: 1.0
**Last Updated**: 2025-11-07
**For**: Admin Users of Micronesian Teachers Digital Library

---

## Table of Contents
1. [Overview](#overview)
2. [When to Use Duplication](#when-to-use-duplication)
3. [How to Duplicate a Book](#how-to-duplicate-a-book)
4. [Bulk Duplication](#bulk-duplication)
5. [What Gets Copied](#what-gets-copied)
6. [What You Need to Fill In](#what-you-need-to-fill-in)
7. [Tips & Best Practices](#tips--best-practices)
8. [Troubleshooting](#troubleshooting)

---

## Overview

The **Book Duplication** feature allows you to quickly create copies of existing books with all their metadata, relationships, and classifications already filled in. This is especially useful for:

- 📚 **Book Series**: Multiple books by the same author with similar metadata
- 🔤 **Language Editions**: Same book in different languages
- 📖 **Different Editions**: New editions of the same book
- 👥 **Same Author/Illustrator**: Books with the same creators
- 🏷️ **Same Collection**: Books in the same series or collection

**Time Savings**: Instead of 10-15 minutes to enter a new book from scratch, duplication reduces it to **~1 minute** for similar books!

---

## When to Use Duplication

### ✅ Perfect Use Cases

1. **Book Series (Same Author & Publisher)**
   - Example: "Reading Book 1", "Reading Book 2", "Reading Book 3"
   - All have the same author, illustrator, publisher, collection, language, and classifications
   - Only title and year differ

2. **Same Content, Multiple Languages**
   - Example: A story available in Chuukese, Pohnpeian, and English
   - All metadata is the same except language and title

3. **Different Editions**
   - Example: 2020 edition and 2024 edition of the same textbook
   - Same content, different publication year

4. **Books by Same Author**
   - All books by "John Smith" share similar subjects and grades
   - Saves time selecting authors and classifications

### ❌ When NOT to Use Duplication

- Books that are completely different (different author, subject, grade, etc.)
- First book in a new series (no template to duplicate from)
- Books with unique metadata that won't be similar to others

---

## How to Duplicate a Book

### Method 1: From the Book List

1. **Navigate** to the Books page in the admin panel
2. **Find** the book you want to duplicate
3. **Click** the three-dot menu (⋮) on the right side of the book row
4. **Select** "Duplicate" from the menu

   ![Duplicate from List](./docs/images/duplicate-list.png)

5. **Confirm** the duplication in the modal that appears
   - The modal shows what will be copied
   - Click "Duplicate" to proceed

6. **Edit** the new duplicate
   - A success notification appears
   - Click "Edit New Book" to immediately edit the duplicate
   - Fill in the required fields (see below)

**Time**: ~30 seconds

---

### Method 2: From the Edit Page

1. **Open** any book in edit mode
2. **Click** the "Duplicate This Book" button in the top right (blue button with document icon)

   ![Duplicate from Edit](./docs/images/duplicate-edit.png)

3. **Confirm** the duplication
4. **Automatically redirected** to edit the new duplicate
5. **Fill in** the required fields

**Time**: ~20 seconds

---

### Method 3: Bulk Duplication (Multiple Books)

1. **Navigate** to the Books page
2. **Select** multiple books using the checkboxes
3. **Click** "Bulk Actions" dropdown at the top
4. **Select** "Duplicate Selected"

   ![Bulk Duplicate](./docs/images/bulk-duplicate.png)

5. **Confirm** the bulk duplication
   - The system duplicates all selected books at once
6. **Review** the success notification
   - Shows how many succeeded and how many failed (if any)

**Time**: ~1 minute for 10 books

---

## What Gets Copied

When you duplicate a book, the following are **automatically copied** from the original:

### ✅ Copied Automatically

#### Relationships
- ✅ **Authors** (all authors with their order)
- ✅ **Illustrators** (all illustrators with their order)
- ✅ **Editors** (all editors with their order)
- ✅ **Languages** (all languages, including primary language flag)
- ✅ **Geographic Locations** (islands, states)
- ✅ **Keywords** (all keyword tags)

#### Classifications
- ✅ **Purpose** (all selected purposes)
- ✅ **Genre** (all selected genres)
- ✅ **Sub-genre** (all selected sub-genres)
- ✅ **Type** (all selected types)
- ✅ **Themes/Uses** (all selected themes)
- ✅ **Learner Level** (all selected learner levels)

#### Metadata
- ✅ **Publisher**
- ✅ **Collection/Series**
- ✅ **Physical Type** (book, journal, magazine, etc.)
- ✅ **Subtitle** (can be edited)
- ✅ **Publication Year** (should be updated)
- ✅ **Pages** (should be updated if different)
- ✅ **Description** (⚠️ **REVIEW REQUIRED**)
- ✅ **Table of Contents** (can be edited)
- ✅ **Notes** (issue and content)
- ✅ **Contact Information**
- ✅ **VLA Standard & Benchmark**
- ✅ **Access Level** (full, limited, unavailable)
- ✅ **Featured & Active Status**

### ❌ NOT Copied (You Must Add)

The following fields are **cleared** and must be filled in manually:

- ❌ **Title** (⚠️ **REQUIRED** - must be unique)
- ❌ **Internal ID** (auto-generated or manual)
- ❌ **PALM Code** (must be unique)
- ❌ **PDF File** (must be uploaded separately)
- ❌ **Thumbnail Image** (must be uploaded separately)
- ❌ **View Count** (reset to 0)
- ❌ **Download Count** (reset to 0)

---

## What You Need to Fill In

After duplicating a book, you **must** fill in the following fields before saving:

### 🔴 Required Fields

1. **Title** ⭐ **REQUIRED**
   - Enter the new book's title
   - Example: If you duplicated "Reading Book 1", enter "Reading Book 2"

2. **PDF File** (if available)
   - Upload the PDF for the new book
   - Files are NOT copied for safety reasons

3. **Thumbnail Image** (if available)
   - Upload or generate a thumbnail for the new book

### 🟡 Recommended to Review

1. **Publication Year**
   - Update if the book is from a different year
   - Copied from original, but often needs updating

2. **Subtitle**
   - Review and update if different
   - May follow a pattern (e.g., "Part 1" → "Part 2")

3. **Pages**
   - Update if the new book has a different page count

4. **Description** ⚠️ **IMPORTANT**
   - **Always review** the description
   - Change any references to the original book's title, edition, or specific details
   - This is the most common place for errors!

5. **Table of Contents**
   - Update if the chapters or sections are different

### ⚠️ Important Warning

Always check the **blue information banner** at the top of the edit form:

> 📋 This book is a duplicate
>
> Duplicated from: "Original Book Title"
>
> ℹ️ All relationships and classifications were copied from the original book. Please review all fields to ensure accuracy.

This reminds you to review critical fields!

---

## Tips & Best Practices

### 📝 General Tips

1. **Duplicate in Batches**
   - If you have 5 books in a series, duplicate all at once using bulk duplication
   - Then edit each one individually

2. **Use Consistent Naming**
   - Follow a pattern: "Title: Book 1", "Title: Book 2", etc.
   - Makes it easier to find books later

3. **Save Frequently**
   - Use "Save and Continue Editing" to save your progress
   - Prevents losing work if something goes wrong

4. **Check the Source Book First**
   - Before duplicating, make sure the source book has correct metadata
   - Errors in the source will be copied to all duplicates!

### 📚 For Book Series

**Best Practice Workflow**:

1. Create the **first book** in the series with complete metadata
2. **Duplicate** it for the second book
3. Change:
   - Title (e.g., "Book 1" → "Book 2")
   - Year (if different)
   - PDF file
   - Thumbnail
   - Description (update references)
4. Save
5. **Duplicate again** for the third book
6. Repeat

**Time Saved**: 10 books in a series = **~90 minutes saved** (vs. entering from scratch)

### 🌍 For Multilingual Books

**Best Practice Workflow**:

1. Create the book in the **primary language**
2. **Duplicate** for the second language
3. Change:
   - Title (translated)
   - Language (change from English to Chuukese, etc.)
   - PDF file (different language version)
   - Thumbnail (if different)
4. Leave everything else the same (subjects, grades, etc. are the same across languages)

### 🔗 Linking Related Books

After duplicating books that are related (editions, languages, etc.):

1. Go to the original book's edit page
2. Scroll to **"Book Relationships"** section
3. Add a relationship:
   - Type: "Same Language" or "Other Language Version" or "Same Version"
   - Related Book: Select the duplicate you just created
4. Save

This helps users find related books!

---

## Visual Indicators

### In the Book List

**Duplicate Badge**:
- Books that are duplicates show a blue "Duplicate" badge in the Status column
- The title description shows: "📋 Duplicated from: [Original Book Title]"

**Source Book Indicator**:
- Books that have been duplicated show: "✨ Duplicated X time(s)" under the title
- This helps you find the "master" book for a series

### In the Edit Form

**Information Banner**:
- A blue banner at the top shows:
  - Which book this was duplicated from (with a link)
  - When it was duplicated
  - A reminder to review all fields

---

## Troubleshooting

### Problem: "Cannot Duplicate Book" Error

**Possible Causes**:
1. Book is missing a required language
2. Book has incomplete metadata

**Solution**:
1. Go back to the original book
2. Add at least one language in the "Relationships" section
3. Save the original book
4. Try duplicating again

---

### Problem: Duplicate Button Not Visible

**Possible Causes**:
1. You don't have admin permissions
2. You're on the wrong page

**Solution**:
1. Make sure you're logged in as an admin
2. Navigate to the Books list page in the admin panel
3. Look for the three-dot menu (⋮) on each book row

---

### Problem: "Duplication Failed" Error

**Possible Causes**:
1. Database connection issue
2. Corrupted data in the original book

**Solution**:
1. Try again (may be a temporary issue)
2. If it persists, contact your system administrator
3. Provide the error message from the red notification

---

### Problem: Forgot to Change the Title

**Symptom**:
- Saved the duplicate without a title (field was blank)
- Form won't save: "The title field is required"

**Solution**:
1. Scroll to the top of the form
2. Fill in the **Title** field (it's marked with a red asterisk *)
3. Save again

---

### Problem: Description Still References Original Book

**Symptom**:
- Description says "This is Reading Book 1..." but you're editing Reading Book 2

**Solution**:
1. Scroll to **"Content & Description"** section
2. Edit the **Description** field
3. Update any references to the original book's title or details
4. Save

**Prevention**:
- Always review the description field after duplication
- Look for the blue information banner reminding you to review fields

---

### Problem: PDF Not Showing for Duplicate

**Symptom**:
- Duplicated book has no PDF file

**Explanation**:
- Files are **intentionally not copied** for safety
- This prevents two books from pointing to the same PDF file

**Solution**:
1. Scroll to **"Book Files"** section
2. Click **"Add File"**
3. Upload the PDF for the new book
4. Mark it as "Primary"
5. Save

---

### Problem: Bulk Duplication - Some Failed

**Symptom**:
- Notification says "Duplicated 8 book(s). 2 book(s) failed to duplicate."

**Solution**:
1. Check the error notification for details
2. Common reasons:
   - Some books missing required languages
   - Database connection timeout
3. Duplicate the failed books individually
4. Check each failed book for missing required fields

---

## FAQ

### Q: How do I know which books I've duplicated?

**A**: Look for the blue "Duplicate" badge in the book list, or check the description under the title.

### Q: Can I duplicate a book that's already a duplicate?

**A**: Yes, but you'll see a warning. It's better to duplicate from the original source book to maintain consistency.

### Q: Will duplicating a book copy its ratings and reviews?

**A**: No. Ratings, reviews, bookmarks, and user notes are NOT copied. Each duplicate starts fresh.

### Q: Can I undo a duplication?

**A**: Yes, simply delete the duplicate book. The original is not affected.

### Q: How many books can I duplicate at once?

**A**: You can select and duplicate multiple books at once using the bulk action. There's no hard limit, but duplicating 50+ books at once may be slow.

### Q: Will the duplicate have the same URL as the original?

**A**: No. Each book has a unique URL based on its slug (generated from the title).

---

## Quick Reference Card

### Duplication Checklist

After duplicating a book, follow this checklist:

- [ ] **Title** - Update to the new book's title ⭐ REQUIRED
- [ ] **Publication Year** - Update if different
- [ ] **Description** - Review and update references ⚠️ IMPORTANT
- [ ] **PDF File** - Upload the new book's PDF
- [ ] **Thumbnail** - Upload or generate thumbnail
- [ ] **Pages** - Update if different
- [ ] **Subtitle** - Update if different
- [ ] **Table of Contents** - Update if different
- [ ] **Notes** - Check for references to original book
- [ ] **Save** - Don't forget to save!

### Keyboard Shortcuts

- `Ctrl+S` or `Cmd+S` - Save and continue editing
- `Esc` - Close modal
- `Tab` - Move to next field

---

## Need Help?

If you encounter issues not covered in this guide:

1. **Check the notification messages** - They often contain helpful error details
2. **Contact your system administrator**
3. **Report bugs** at the project repository

---

**Remember**: Duplication saves time, but always **review the fields** to ensure accuracy! The goal is to reduce data entry from 10-15 minutes to just 1 minute, while maintaining high data quality.

Happy duplicating! 📚✨
