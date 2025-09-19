<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>img:is([sizes="auto" i], [sizes^="auto," i]) {
        contain-intrinsic-size: 3000px 1500px
    }</style>

    <!-- SEO Meta Tags -->
    @if(isset($seoData))
        {!! $seoData['meta_tags'] !!}
        @if(isset($seoData['structured_data']))
            <script type="application/ld+json">{!! $seoData['structured_data'] !!}</script>
        @endif
    @else
        <title>@yield('title', 'Micronesian Teachers Digital Library')</title>
        <meta name="description" content="@yield('description', 'Aloha and welcome to the Micronesian Teachers Digital Library! This comprehensive collection provides educators across Micronesia with access to over 2,000 educational books in local languages, fostering cultural preservation and educational excellence.')">

        <!-- Open Graph -->
        <meta property="og:locale" content="{{ app()->getLocale() }}"/>
        <meta property="og:type" content="website"/>
        <meta property="og:title" content="@yield('title', 'Micronesian Teachers Digital Library')"/>
        <meta property="og:description" content="@yield('description', 'Comprehensive collection of over 2,000 educational books in local languages for Micronesian educators.')"/>
        <meta property="og:url" content="{{ url()->current() }}"/>
        <meta property="og:site_name" content="Micronesian Teachers Digital Library"/>

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image"/>
        <meta name="twitter:title" content="@yield('title', 'Micronesian Teachers Digital Library')"/>
        <meta name="twitter:description" content="@yield('description', 'Comprehensive collection of over 2,000 educational books in local languages for Micronesian educators.')"/>
    @endif

    <!-- DNS Prefetch -->
    <link rel='dns-prefetch' href='//www.googletagmanager.com'/>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.15.4/css/all.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Styles -->
    @stack('styles')
    <style>
        /* WordPress-compatible CSS variables for existing templates */
        :root {
            --wp-primary-color: #2563eb;
            --wp-secondary-color: #64748b;
            --wp-success-color: #059669;
            --wp-warning-color: #d97706;
            --wp-danger-color: #dc2626;
            --wp-text-color: #1f2937;
            --wp-text-light: #6b7280;
            --wp-border-color: #e5e7eb;
            --wp-background: #ffffff;
            --wp-background-light: #f9fafb;
        }

        /* Ensure existing CSS classes work */
        .container {
            @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
        }

        .with_sidebar {
            @apply grid lg:grid-cols-4 gap-8;
        }

        .main_content {
            @apply lg:col-span-3;
        }

        .sidebar {
            @apply lg:col-span-1;
        }

        /* Header styles */
        .coe-banner {
            @apply bg-blue-900 py-3;
        }

        .header-container {
            @apply container flex justify-between items-center;
        }

        .left-institution, .right-institution {
            @apply flex items-center space-x-3;
        }

        .left-institution a, .right-institution a {
            @apply flex items-center space-x-3 text-white hover:text-blue-200 transition-colors;
        }

        .institution-name {
            @apply hidden sm:block text-sm font-medium;
        }

        .program-banner {
            @apply bg-white shadow-sm border-b;
        }

        .module-toggle {
            @apply bg-gray-50 py-2;
        }

        .toggle-container {
            @apply container flex justify-center items-center space-x-4;
        }

        .toggle-btn {
            @apply px-6 py-2 rounded-lg font-medium transition-colors;
        }

        .toggle-btn.active {
            @apply bg-blue-600 text-white;
        }

        .toggle-btn:not(.active) {
            @apply text-gray-600 hover:text-blue-600;
        }

        .toggle-divider {
            @apply text-gray-400;
        }

        .nav-primary {
            @apply py-4;
        }

        .menu-main-container {
            @apply container flex justify-between items-center;
        }

        .nav {
            @apply flex space-x-6;
        }

        .menu-item {
            @apply relative;
        }

        .menu-item > a {
            @apply text-gray-700 hover:text-blue-600 font-medium transition-colors;
        }

        .sub-menu {
            @apply absolute top-full left-0 bg-white shadow-lg rounded-lg py-2 min-w-48 z-50 hidden;
        }

        .menu-item:hover .sub-menu {
            @apply block;
        }

        .sub-menu li a {
            @apply block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600;
        }

        .menu-search {
            @apply flex items-center;
        }

        .search-toggle {
            @apply p-2 text-gray-600 hover:text-blue-600;
        }

        /* Footer styles */
        .department-footer {
            @apply bg-blue-900 text-white py-8;
        }

        .department-footer .container {
            @apply text-center;
        }

        .dept-logo {
            @apply mx-auto mb-4 h-16;
        }

        .tagline {
            @apply text-blue-100 max-w-4xl mx-auto text-lg;
        }

        .site-footer {
            @apply bg-gray-900 text-gray-300 py-8;
        }

        .address-contact-link {
            @apply grid md:grid-cols-4 gap-6 mb-8;
        }

        .contact {
            @apply space-y-2;
        }

        .other-links-col {
            @apply space-y-2;
        }

        .other-links-col a {
            @apply text-gray-400 hover:text-white transition-colors;
        }

        .logos-coe-links {
            @apply flex justify-between items-center pt-6 border-t border-gray-700;
        }

        .logos {
            @apply flex space-x-4;
        }

        .links {
            @apply flex space-x-4;
        }

        .links a {
            @apply text-gray-400 hover:text-white transition-colors;
        }

        /* Main content styles */
        .main {
            @apply min-h-screen;
        }

        .title_banner {
            @apply bg-gradient-to-r from-blue-50 to-indigo-50 py-8;
        }

        .header-blurb {
            @apply space-y-4;
        }

        .breadcrumbs {
            @apply text-sm text-gray-600;
        }

        .handbook-header-menu h1 {
            @apply text-3xl font-bold text-gray-900;
        }

        .header-image {
            @apply flex items-center justify-center space-x-4;
        }

        .page-content {
            @apply py-8;
        }

        .section {
            @apply mb-8;
        }

        .section-title {
            @apply text-center py-6;
        }

        .section-title h2 {
            @apply text-2xl font-bold text-gray-900;
        }

        .divider {
            @apply mx-auto mb-4 h-2;
        }

        .wysiwyg {
            @apply prose prose-lg max-w-none;
        }

        /* Mobile menu */
        .menu-toggle {
            @apply md:hidden p-2 text-gray-600;
        }

        @media (max-width: 768px) {
            .nav {
                @apply flex-col space-x-0 space-y-2;
            }

            .menu-main-container {
                @apply flex-col items-start;
            }
        }
    </style>
</head>

<body class="wp-singular page-template page-template-template-stems2-ulu-guide template-stems2-ulu-guide page page-id-4059 wp-theme-coe-wp-programs-themeresources edcs program stems2 ulu-education-toolkit-guide sidebar-primary app-data index-data singular-data page-data page-4059-data page-ulu-education-toolkit-guide-data template-stems2-ulu-guide-data">

<header class="banner" role="banner">
    <a class="screen-reader-text skip-link" href="#content">Skip to main content</a>

    <!-- TOP BAR with two institution logos -->
    <div class="coe-banner">
        <div class="header-container">
            <div class="left-institution">
                <a href="{{ route('cms.page', 'home') }}" title="Educational Institution 1">
                    <img src="https://picsum.photos/120/60?random=10" alt="Institution 1 Logo" class="h-8">
                    <span class="institution-name">Educational Institution 1</span>
                </a>
            </div>
            <div class="right-institution">
                <a href="{{ route('cms.page', 'home') }}" title="Educational Institution 2">
                    <img src="https://picsum.photos/120/60?random=11" alt="Institution 2 Logo" class="h-8">
                    <span class="institution-name">Educational Institution 2</span>
                </a>
            </div>
        </div>
    </div>

    <!-- MENU BAR with Guide/Library toggle -->
    <div class="program-banner">
        <div class="module-toggle">
            <div class="toggle-container">
                <button class="toggle-btn {{ request()->routeIs('cms.page') ? 'active' : '' }}" data-module="guide">
                    Resource guide
                </button>
                <div class="toggle-divider">|</div>
                <button class="toggle-btn {{ request()->routeIs('cms.category') ? 'active' : '' }}" data-module="library" onclick="window.location.href='{{ route('cms.category', 'all') }}'">
                    Resource library
                </button>
            </div>
        </div>

        <nav aria-label="main menu" class="nav-primary">
            <button class="menu-toggle" aria-expanded="false">
                Menu <span class="screen-reader-text">Open Mobile Menu</span>
            </button>

            <div class="menu-main-container">
                <ul id="menu-main" class="nav">
                    <li class="menu-item">
                        <a href="{{ route('cms.page', 'home') }}">Home</a>
                    </li>
                    <li class="menu-item menu-item-has-children">
                        <a href="#">About Us</a>
                        <ul class="sub-menu">
                            <li><a href="{{ route('cms.page', 'about') }}">What is MTDL?</a></li>
                            <li><a href="{{ route('cms.page', 'why') }}">Why Digital Library?</a></li>
                            <li><a href="{{ route('cms.page', 'gallery') }}">Photo Gallery</a></li>
                        </ul>
                    </li>
                    <li class="menu-item menu-language menu-item-has-children">
                        <a href="#" class="language-selector">
                            EN <i class="fal fa-chevron-down"></i>
                        </a>
                        <ul class="sub-menu language-options">
                            <li><a href="#">English</a></li>
                            <li><a href="#">Chuukese</a></li>
                            <li><a href="#">Pohnpeian</a></li>
                            <li><a href="#">Yapese</a></li>
                            <li><a href="#">Kosraean</a></li>
                            <li><a href="#">Marshallese</a></li>
                        </ul>
                    </li>
                    @auth
                        <li class="menu-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="menu-item">
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                                    Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="menu-item menu-login">
                            <a href="{{ route('login') }}">Login</a>
                        </li>
                    @endauth
                </ul>

                <div class="menu-search">
                    <a class="search-toggle" href="#searchform">
                        <i class="fal fa-search" aria-hidden="true"></i>
                        <span class="screen-reader-text">Toggle Search</span>
                    </a>
                    <div class="search-container" aria-expanded="false">
                        <form role="search" method="get" class="searchform" action="{{ route('cms.search') }}">
                            <label for="basic-site-search" id="searchform" class="screen-reader-text">
                                Search for:
                            </label>
                            <input type="search" class="search-field" id="basic-site-search"
                                   placeholder="Search &hellip;" value="{{ request('q') }}" name="q"/>
                            <button type="submit" class="search-submit" value="Search">
                                <i class="fal fa-search" aria-hidden="true"></i>
                                <span class="screen-reader-text">Site Search</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>

<main class="main @yield('main_class', 'with_sidebar print')" id="content">
    @yield('content')
</main>

<footer class="department-footer" aria-label="department or special program info">
    <div class="container">
        <img class="dept-logo" src="https://picsum.photos/200/80?random=3DL" alt="Micronesian Teachers Digital Library logo">
        <p class="tagline">
            The Micronesian Teachers Digital Library promotes culturally responsive, place-based education by providing access to over 2,000 educational resources in local languages, fostering cultural preservation and academic excellence across Micronesia.
        </p>
    </div>
</footer>

<footer class="site-footer">
    <div class="container">
        <div class="address-contact-link">
            <address>
                <strong>Micronesian Teachers Digital Library</strong>
                <p>Educational Resource Center<br/>
                    Pacific Islands Region<br/>
                    Micronesia</p>
            </address>
            <div class="contact">
                <i class="fas fa-phone" aria-hidden="true"></i> <a href="tel:+1-000-000-0000">(000) 000-0000</a>
                <br/><i class="fas fa-fax" aria-hidden="true"></i> <a href="tel:+1-000-000-0000">(000) 000-0000</a>
                <br/><i class="fas fa-envelope" aria-hidden="true"></i> <a href="mailto:info@mtdl.edu">info@mtdl.edu</a>
            </div>

            <ul class="other-links-col other-links-col1">
                <li><a href="{{ route('cms.page', 'accessibility') }}" target="_self">Accessibility Info</a></li>
                <li><a href="{{ route('cms.page', 'suggest-content') }}" target="_self">Suggest Educational Content</a></li>
            </ul>
            <ul class="other-links-col other-links-col2">
                <li><a href="{{ route('cms.page', 'contribute') }}" target="_self">Contributor Form</a></li>
                <li><a href="{{ route('cms.page', 'submit-resources') }}" target="_self">Submit Resources</a></li>
            </ul>
        </div>
        <div class="logos-coe-links">
            <div class="logos">
                <a class="coe" href="{{ route('cms.page', 'home') }}">
                    <img src="https://picsum.photos/200/80?random=80" alt="MTDL logo" class="h-12">
                </a>
                <a class="manoa" href="{{ route('cms.page', 'home') }}">
                    <img src="https://picsum.photos/200/80?random=81" alt="Education Initiative logo" class="h-12">
                </a>
            </div>
            <div class="links">
                <a class="sitemap" href="{{ route('cms.sitemap') }}" title="sitemap">
                    <i aria-hidden="true" class="fal fa-sitemap"></i> <span>Sitemap</span>
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" title="user dashboard">
                        <i aria-hidden="true" class="fal fa-user"></i> <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" title="user login">
                        <i aria-hidden="true" class="fal fa-sign-in"></i> <span>Log In</span>
                    </a>
                @endauth
            </div>
        </div>

        <div class="text-center text-gray-500 text-sm mt-6 pt-6 border-t border-gray-700">
            <p>&copy; {{ date('Y') }} Micronesian Teachers Digital Library. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Terms of Use Modal -->
@include('cms.partials.terms-modal')

<!-- Scripts -->
@stack('scripts')

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const menuContainer = document.querySelector('.menu-main-container .nav');

        if (menuToggle && menuContainer) {
            menuToggle.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                menuContainer.classList.toggle('hidden');
            });
        }

        // Search toggle
        const searchToggle = document.querySelector('.search-toggle');
        const searchContainer = document.querySelector('.search-container');

        if (searchToggle && searchContainer) {
            searchToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isExpanded = searchContainer.getAttribute('aria-expanded') === 'true';
                searchContainer.setAttribute('aria-expanded', !isExpanded);
                searchContainer.classList.toggle('hidden');

                if (!isExpanded) {
                    searchContainer.querySelector('.search-field')?.focus();
                }
            });
        }

        // Module toggle functionality
        const toggleBtns = document.querySelectorAll('.toggle-btn');
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.classList.contains('active')) {
                    toggleBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
    });
</script>

</body>
</html>