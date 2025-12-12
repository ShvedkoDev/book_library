# CMS Toggle Functionality Guide

## Overview
This guide explains how to add collapsible toggle functionality to CMS page blocks with Font Awesome icons.

## Features
- ✅ Smooth expand/collapse animations
- ✅ Font Awesome icons (download and eye icons)
- ✅ Accessible (ARIA attributes)
- ✅ Smooth scrolling when expanding
- ✅ Hover effects and visual feedback

## How to Use

### 1. Structure Your HTML

Use this structure in your Custom HTML Blocks:

```html
<div class="resource-guide-special-block">
    <h3>YOUR TITLE HERE</h3>

    <!-- Action buttons with Font Awesome icons -->
    <div class="toggle-actions">
        <a href="#" class="download-link">
            <i class="fa-light fa-download"></i>
            Download the entire Resource guide in PDF format
        </a>
        <a href="#" class="toggle-trigger" data-toggle="content-block-id" aria-expanded="false">
            <i class="fa-light fa-eye"></i>
            See practical tips on making the best use of the Resource Guide
        </a>
    </div>

    <!-- Collapsible content -->
    <div id="content-block-id" class="toggle-content">
        <!-- Your hidden content goes here -->
        <p>This content will be hidden by default and shown when clicking the eye icon link.</p>
        <!-- Add as much content as needed -->
    </div>
</div>
```

### 2. Updated HTML for "HOW TO USE THIS RESOURCE GUIDE?"

Replace the existing block_2 HTML in the admin panel with this:

```html
<div class="resource-guide-special-block">
    <h3>HOW TO USE THIS RESOURCE GUIDE?</h3>

    <div class="toggle-actions">
        <a href="#" class="download-link">
            <i class="fa-light fa-download"></i>
            Download the entire Resource guide in the PDF format
        </a>
        <a href="#" class="toggle-trigger" data-toggle="guide-content" aria-expanded="false">
            <i class="fa-light fa-eye"></i>
            See practical tips on making the best use of the Resource Guide and maximizing its benefits
        </a>
    </div>

    <div id="guide-content" class="toggle-content">
        <p>This <strong><em>Resource Guide</em></strong> opens with a <a href="/framework">guiding framework</a> that provides an overarching perspective on Vernacular Language Arts. It then applies this framework to <a href="/principles">principles of place-based education</a>. Next, the guide presents experiential <a href="/strategy">learning and teaching strategies</a> and suggests ways to adapt them to support curriculum delivery. Color-coding highlights thematic links among the framework, principles, and strategies, helping teachers stay mindful of the four recurring themes throughout the VLA curriculum and keep them central in planning and practice.</p>

        <p>After reviewing the above parts individually or with colleagues, teachers may hold small-group discussions and revisit these core parts of the guide. Using the objectives in the <a href="/framework">guiding framework</a>, they can explore what place-based education means in their local context and compare their reflections with the fundamental <a href="/principles">principles of place-based education</a>. Following these discussions, teachers can engage with the <a href="/strategy">learning and teaching strategies</a>.</p>

        <p>Having explored the above sections thoroughly and inspired by ideas generated through collaboration, educators can consider <a href="/applying-by-standard">applying the teaching and learning strategies to the framework and principles, organized by standard</a>.</p>

        <p>Teachers will also want to examine the range of available <a href="/toolkit">sample teaching and assessment tools</a>, which they print out and use directly or modify for their needs. Though the "toolkit" has a variety of materials, many additional examples and resources exist beyond those included and will continue to emerge as teachers communicate and collaborate.</p>

        <p>Teachers are invited to refer to the <a href="/glossary">glossary</a> whenever they come across unfamiliar terms in this guide, the FSM VLA curriculum, or the <a href="/references">recommended readings and references</a>.</p>

        <p>Finally, before beginning their first search for available VLA materials, teachers are encouraged to consult the guidance below on how to use the Resource Library effectively.</p>
    </div>
</div>
```

### 3. Updated HTML for "HOW TO USE THE RESOURCE LIBRARY?"

Replace the existing block_3 HTML with this:

```html
<div class="resource-guide-special-block">
    <h3>HOW TO USE THE RESOURCE LIBRARY?</h3>

    <div class="toggle-actions">
        <a href="#" class="download-link">
            <i class="fa-light fa-download"></i>
            Download a printable PDF version of the tips on how to use the <em>Resource library</em>
        </a>
        <a href="#" class="toggle-trigger" data-toggle="library-content" aria-expanded="false">
            <i class="fa-light fa-eye"></i>
            See practical tips on making the most of the Resource Library and easily finding what you need
        </a>
    </div>

    <div id="library-content" class="toggle-content">
        <!-- All your existing library guide content goes here -->
        <p>The <strong><em>Resource library</em></strong> supporting the National Vernacular Language Arts curriculum is a comprehensive, well-organized online repository of place-based educational materials...</p>
        <!-- ... rest of the content ... -->
    </div>
</div>
```

## How to Apply This to Your CMS Pages

1. Go to `/admin/pages`
2. Edit the "Introduction" page (or whichever page contains these blocks)
3. Scroll to "Custom HTML Blocks" section
4. Find the block you want to update (e.g., `block_2`)
5. Replace the HTML content with the updated version above
6. Save the page
7. View the page on the frontend to test the toggle functionality

## Font Awesome Icons Used

- `<i class="fa-light fa-download"></i>` - Download icon
- `<i class="fa-light fa-eye"></i>` - Eye/view icon

You can use any other Font Awesome Light icons by changing the icon class.

## CSS Classes Reference

- `.toggle-actions` - Container for action buttons
- `.toggle-trigger` - The clickable link that toggles content
- `.toggle-content` - The collapsible content container
- `data-toggle="unique-id"` - Links the trigger to its content
- `id="unique-id"` - Identifies the collapsible content

## Important Notes

1. **Unique IDs**: Make sure each toggle has a unique ID (e.g., `guide-content`, `library-content`, `acknowledgments-content`)
2. **data-toggle attribute**: Must match the ID of the content you want to toggle
3. **Hidden by default**: Content inside `.toggle-content` is hidden until the trigger is clicked
4. **Multiple toggles**: You can have multiple toggles on the same page - just use unique IDs

## Example with Multiple Toggles

```html
<!-- First toggle -->
<a href="#" class="toggle-trigger" data-toggle="section1">Show Section 1</a>
<div id="section1" class="toggle-content">Content 1</div>

<!-- Second toggle -->
<a href="#" class="toggle-trigger" data-toggle="section2">Show Section 2</a>
<div id="section2" class="toggle-content">Content 2</div>
```

## Troubleshooting

**Toggle not working?**
- Check that the `data-toggle` value matches the `id` of the content
- Ensure JavaScript is loaded (clear browser cache)
- Check browser console for errors

**Icons not showing?**
- Verify Font Awesome is loaded on the page
- Check that the icon class names are correct

**Animation not smooth?**
- Clear browser cache
- Check that CSS is properly loaded
