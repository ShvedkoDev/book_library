<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'/>

    <title>@yield('title', 'Micronesian Teachers Digital Library')</title>
    <meta name="description" content="@yield('description', 'Access over 2,000 educational resources in local languages for Micronesian educators')">

    <!-- Open Graph / SEO -->
    <meta property="og:locale" content="en_US"/>
    <meta property="og:type" content="@yield('og_type', 'website')"/>
    <meta property="og:title" content="@yield('title', 'Micronesian Teachers Digital Library')"/>
    <meta property="og:description" content="@yield('description', 'Access over 2,000 educational resources in local languages for Micronesian educators')"/>
    <meta property="og:url" content="{{ url()->current() }}"/>
    <meta property="og:site_name" content="Micronesian Teachers Digital Library"/>
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')"/>
        <meta property="og:image:alt" content="@yield('title', 'Micronesian Teachers Digital Library')"/>
    @endif

    <!-- Stylesheets -->
    <link rel='stylesheet' href='{{ asset('library-assets/css/main.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/fonts.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/styles.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/font-awesome.min.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/wordpress-main.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/library-custom.css') }}' type='text/css' media='all'/>

    @stack('styles')
</head>

<body class="wp-singular page-template page-template-template-stems2-ulu-guide template-stems2-ulu-guide page wp-theme-coe-wp-programs-themeresources edcs program stems2 ulu-education-toolkit-guide sidebar-primary app-data index-data singular-data page-data library-layout">

<div id="wrapper">
    <!-- Header Banner -->
    <header class="banner" role="banner">
        <a class="screen-reader-text skip-link" href="#content">Skip to main content</a>

        <!-- COE Banner with logos and language selector -->
        <div class="coe-banner">
            <div class="header-container">
                <div class="left-institution">
                    <img src="{{ asset('library-assets/images/government_of_the_federated_states_of_micronesia.png') }}" alt="Government of the Federated States of Micronesia">
                </div>
                <div class="right-institution">
                    <div class="language-selector-container">
                        <button class="language-selector" id="languageSelector">
                            <span class="language-code">ENG</span>
                            <i class="fal fa-chevron-down"></i>
                        </button>
                        <ul class="language-dropdown" id="languageDropdown">
                            <li><a href="#" data-code="eng" data-enabled="true">English</a></li>
                            <li><a href="#" data-code="chk" data-enabled="false" class="disabled">Chuukese</a></li>
                            <li><a href="#" data-code="kpg" data-enabled="false" class="disabled">Kapingamarangi</a></li>
                            <li><a href="#" data-code="kos" data-enabled="false" class="disabled">Kosraean</a></li>
                            <li><a href="#" data-code="mrl" data-enabled="false" class="disabled">Mortlockese</a></li>
                            <li><a href="#" data-code="mwv" data-enabled="false" class="disabled">Mwoakilloaese</a></li>
                            <li><a href="#" data-code="nkr" data-enabled="false" class="disabled">Nukuoro</a></li>
                            <li><a href="#" data-code="pif" data-enabled="false" class="disabled">Pingelapese</a></li>
                            <li><a href="#" data-code="pon" data-enabled="false" class="disabled">Pohnpeian</a></li>
                            <li><a href="#" data-code="stw" data-enabled="false" class="disabled">Satawalese</a></li>
                            <li><a href="#" data-code="uli" data-enabled="false" class="disabled">Ulithian</a></li>
                            <li><a href="#" data-code="woe" data-enabled="false" class="disabled">Woleaian</a></li>
                            <li><a href="#" data-code="yap" data-enabled="false" class="disabled">Yapese</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Program Banner with toggle and navigation -->
        <div class="program-banner">
            <div class="module-toggle">
                <div class="toggle-switch-container">
                    <div class="toggle-switch">
                        <button class="toggle-option {{ request()->is('/') ? 'active' : '' }}" onclick="window.location.href='{{ url('/') }}'" data-module="guide">Resource guide</button>
                        @auth
                            <button class="toggle-option {{ request()->is('library*') ? 'active' : '' }}" onclick="window.location.href='{{ route('library.index') }}'" data-module="library">Resource library</button>
                        @else
                            <button class="toggle-option {{ request()->is('library*') ? 'active' : '' }}" onclick="window.location.href='{{ route('library.index') }}'" data-module="library" title="Login required to access library">
                                Resource library <i class="fal fa-lock" style="font-size: 0.8em; margin-left: 0.25em;"></i>
                            </button>
                        @endauth
                    </div>
                </div>
            </div>
            <nav aria-label="main menu" class="nav-primary">

                <button class="menu-toggle" aria-expanded="false">Menu <span class="screen-reader-text">Open Mobile Menu</span></button>
                <div class="menu-main-container">
                    <ul id="menu-main" class="nav">
                        <li id="menu-item-2335" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home menu-item-2335"><a href="{{ url('/') }}">Home</a></li>
                        <li id="menu-item-1362" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1362"><a href="#">About Us</a>
                            <ul class="sub-menu">
                                <li id="menu-item-314" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-314"><a href="{{ url('/about') }}">What is MTDL?</a></li>
                                <li id="menu-item-320" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-320"><a href="{{ url('/why') }}">Why Digital Library?</a></li>
                                <li id="menu-item-1692" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1692"><a href="{{ url('/photo-gallery') }}">Photo Gallery</a></li>
                            </ul>
                        </li>
                        @auth
                            <li class="menu-item menu-login"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="menu-item menu-logout">
                                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                    @csrf
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">Logout</a>
                                </form>
                            </li>
                        @else
                            <li class="menu-item menu-login"><a href="{{ route('login') }}">Login</a></li>
                        @endauth
                    </ul>
                </div>

                <div class="menu-search">
                    <a class="search-toggle" href="#searchform"><i class="fal fa-search" aria-hidden="true"></i><span class="screen-reader-text">Toggle Search</span></a>
                    <div class="search-container" aria-expanded="false">
                        <form role="search" method="get" class="searchform" action="/">
                            <label for="basic-site-search" id="searchform" class="screen-reader-text">
                                Search for:
                            </label>
                            <input type="search" class="search-field" id="basic-site-search" placeholder="Search &hellip;" value="" name="s"/>
                            <button type="submit" class="search-submit" value="Search"><i class="fal fa-search" aria-hidden="true"></i><span class="screen-reader-text">Site Search</span></button>
                        </form>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main with_sidebar print" id="content">
        @yield('content')
    </main>

</div>

<!-- Department Footer -->
<footer class="department-footer" aria-label="department or special program info">
    <div class="container">
        <img class="dept-logo" src="https://picsum.photos/200/80?random=3DL" alt="Micronesian Teachers Digital Library logo">
        <p class="tagline">
            The Micronesian Teachers Digital Library promotes culturally responsive, place-based education by providing access to over 2,000 educational resources in local languages, fostering cultural preservation and academic excellence across Micronesia.
        </p>
    </div>
</footer>

<!-- Site Footer -->
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
                <li><a href="#" target="_self">Accessibility Info</a></li>
                <li><a href="#" target="_self">Suggest Educational Content</a></li>
            </ul>
            <ul class="other-links-col other-links-col2">
                <li><a href="#" target="_self">Contributor Form</a></li>
                <li><a href="#" target="_self">Submit Resources</a></li>
            </ul>
        </div>
        <div class="logos-coe-links">
            <div class="logos">
                <a class="coe" href="{{ url('/') }}">
                    <img src="{{ asset('library-assets/images/mtdl-logo.png') }}" alt="MTDL logo">
                </a>
                <a class="manoa" href="{{ url('/') }}">
                    <img src="{{ asset('library-assets/images/education-initiative-logo.png') }}" alt="Education Initiative logo">
                </a>
            </div>
            <div class="links">

                <a class="sitemap" href="{{ url('/sitemap') }}" title="sitemap"><i aria-hidden="true" class="fal fa-sitemap"></i> <span>Sitemap</span></a>
                <a href="{{ route('login') }}" title="user login"><i aria-hidden="true" class="fal fa-sign-in"></i> <span>Log In</span></a>
            </div>
        </div>
    </div>
</footer>

<script>
    // Language Selector Dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const languageSelector = document.getElementById('languageSelector');
        const languageDropdown = document.getElementById('languageDropdown');

        if (languageSelector && languageDropdown) {
            languageSelector.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                languageDropdown.classList.toggle('show');
                this.setAttribute('aria-expanded', languageDropdown.classList.contains('show'));
            });

            // Handle language selection
            languageDropdown.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    if (this.classList.contains('disabled')) {
                        e.preventDefault();
                        return false;
                    }

                    const code = this.getAttribute('data-code');
                    const languageCode = document.querySelector('.language-code');
                    if (languageCode && code) {
                        languageCode.textContent = code.toUpperCase().substring(0, 3);
                    }

                    languageDropdown.classList.remove('show');
                    languageSelector.setAttribute('aria-expanded', 'false');

                    // TODO: Implement actual language switching logic
                    console.log('Language changed to:', code);
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!languageSelector.contains(e.target) && !languageDropdown.contains(e.target)) {
                    languageDropdown.classList.remove('show');
                    languageSelector.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // Mobile Menu Toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const menuContainer = document.querySelector('.menu-main-container');

        if (menuToggle && menuContainer) {
            menuToggle.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                menuContainer.classList.toggle('active');
            });
        }

        // Search Toggle
        const searchToggle = document.querySelector('.search-toggle');
        const searchContainer = document.querySelector('.search-container');

        if (searchToggle && searchContainer) {
            searchToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isExpanded = searchContainer.getAttribute('aria-expanded') === 'true';
                searchContainer.setAttribute('aria-expanded', !isExpanded);
                searchContainer.classList.toggle('active');

                if (!isExpanded) {
                    const searchField = searchContainer.querySelector('.search-field');
                    if (searchField) {
                        searchField.focus();
                    }
                }
            });
        }
    });
</script>

@stack('scripts')

</body>
</html>
