# MTDL UI Kit - Pure HTML/CSS/JS Template

A self-contained, Laravel/Livewire-ready template for the Micronesian Teachers Digital Library project.

## 🚀 Features

- **100% Self-Contained** - No external dependencies
- **Laravel/Livewire Ready** - Clean structure for easy integration
- **Responsive Design** - Mobile-first approach
- **Modern CSS** - CSS Custom Properties (CSS Variables)
- **Pure JavaScript** - No jQuery dependencies
- **Font Awesome Icons** - Local font files included
- **SVG Placeholders** - Scalable mock images with proper dimensions

## 📁 Project Structure

```
/final/
├── index.html                      # Main template file
├── assets/
│   ├── css/
│   │   ├── main.css               # Main stylesheet with CSS variables
│   │   ├── fonts.css              # Font definitions (Google Fonts)
│   │   └── font-awesome.min.css   # Local Font Awesome CSS
│   ├── js/
│   │   └── main.js                # Pure JavaScript functionality
│   ├── fonts/
│   │   ├── fa-solid-900.woff2     # Font Awesome solid icons
│   │   └── fa-regular-400.woff2   # Font Awesome regular icons
│   └── images/
│       ├── logo-mtdl.svg          # Main MTDL logo
│       ├── logo-institution-1.svg # Institution logo 1
│       ├── logo-institution-2.svg # Institution logo 2
│       ├── divider.png            # Section divider image
│       └── placeholder-generator.html # Reference for image dimensions
└── README.md                       # This file
```

## 🎨 Design System

### Colors
```css
--color-primary: #009877        /* MTDL Green */
--color-primary-dark: #007a5e   /* Dark Green */
--color-primary-light: #e8f5f0  /* Light Green */
--color-secondary: #005a70      /* MTDL Blue */
--color-secondary-dark: #004556 /* Dark Blue */
--color-secondary-light: #e6f2f5 /* Light Blue */
```

### Typography
- **Primary Font**: Montserrat (Google Fonts)
- **Fallback**: System fonts
- **Sizes**: xs (12px) to 4xl (36px)

### Spacing
- **Scale**: xs (4px) to 3xl (64px)
- **Consistent**: Using CSS custom properties

## 🛠️ Laravel/Livewire Integration

### 1. Convert to Blade Templates

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'MTDL Guide' }}</title>

    @vite(['resources/css/main.css', 'resources/js/main.js'])
</head>
<body>
    <!-- Include header component -->
    @include('components.header')

    <!-- Main content -->
    <main>{{ $slot }}</main>

    <!-- Include footer component -->
    @include('components.footer')

    <!-- Include modals -->
    @include('components.terms-modal')
</body>
</html>
```

### 2. Create Livewire Components

```php
// app/Livewire/ModuleToggle.php
class ModuleToggle extends Component
{
    public $currentModule = 'guide';

    public function switchModule($module)
    {
        $this->currentModule = $module;

        if ($module === 'library') {
            return redirect()->route('library.index');
        }

        return redirect()->route('guide.index');
    }
}
```

### 3. Terms Acceptance Component

```php
// app/Livewire/TermsModal.php
class TermsModal extends Component
{
    public $showModal = false;

    public function acceptTerms()
    {
        session(['terms_accepted' => true]);
        $this->showModal = false;
        return redirect()->route('library.index');
    }
}
```

## 📱 JavaScript API

The template includes a clean JavaScript API that can work with Livewire:

```javascript
// Access the main app object
window.MTDL.App.init();

// Check terms acceptance
window.MTDL.TermsModal.hasAcceptedTerms();

// Trigger library entry
window.MTDL.LibraryEntry.handleEntry();
```

## 🎯 Key Features Implemented

### ✅ All PDF Requirements Met
1. **Top Bar** - Institution logos with responsive design
2. **Menu Bar** - Guide/Library toggle with proper styling
3. **Navigation** - Login, language selector, search
4. **Three Library Entry Points**:
   - Menu toggle switch
   - Right sidebar entry
   - Bottom acceptance button
5. **Terms Modal** - Professional modal with localStorage
6. **Footer** - Matching color scheme
7. **Responsive Sidebar** - Contributors and navigation
8. **Landing Page** - Enhanced Guide styling

### 🔧 Technical Features
- **CSS Grid & Flexbox** - Modern layout techniques
- **CSS Custom Properties** - Easy theming
- **Mobile-First** - Responsive breakpoints
- **Accessibility** - ARIA labels and semantic HTML
- **Performance** - Optimized assets and minimal dependencies

## 🚦 Usage Instructions

### For Development
1. Open `index.html` in a web browser
2. All assets are locally served
3. No build process required

### For Laravel Integration
1. Move CSS files to `resources/css/`
2. Move JS files to `resources/js/`
3. Move images to `public/images/`
4. Convert HTML to Blade components
5. Integrate with Livewire components

## 🎨 Customization

### Colors
Edit CSS custom properties in `assets/css/main.css`:

```css
:root {
  --color-primary: #your-color;
  --color-secondary: #your-color;
}
```

### Typography
Update font imports in `assets/css/fonts.css`

### Layout
Modify grid and flexbox properties in main.css

## 📋 Browser Support

- **Modern Browsers**: Chrome 90+, Firefox 90+, Safari 14+, Edge 90+
- **CSS Features**: Grid, Flexbox, Custom Properties
- **JavaScript**: ES6+ features

## 🔍 File Dependencies

### No External Dependencies
- ❌ No CDN links
- ❌ No WordPress dependencies
- ❌ No external font services (except Google Fonts import)
- ✅ All assets included locally

### Asset Sizes
- **main.css**: ~25KB (uncompressed)
- **font-awesome.min.css**: ~100KB
- **main.js**: ~8KB
- **Font files**: ~180KB total
- **Images**: ~15KB total (SVG + PNG)

## 🎯 Next Steps for Laravel Integration

1. **Setup Vite** for asset compilation
2. **Create Blade layouts** from HTML structure
3. **Build Livewire components** for dynamic parts
4. **Add Laravel features**:
   - Authentication (Login button)
   - Language switching
   - Search functionality
   - User preferences
5. **Database integration** for terms acceptance tracking
6. **Admin panel** for content management

This template provides a solid foundation for your Laravel/Livewire application with all the design requirements met and no external dependencies.