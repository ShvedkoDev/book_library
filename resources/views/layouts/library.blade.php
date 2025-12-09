<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'/>

    <title>@yield('title', $siteName)</title>
    <meta name="description" content="@yield('description', $siteDescription)">

    <!-- Open Graph / SEO -->
    <meta property="og:locale" content="en_US"/>
    <meta property="og:type" content="@yield('og_type', 'website')"/>
    <meta property="og:title" content="@yield('title', $siteName)"/>
    <meta property="og:description" content="@yield('description', $siteDescription)"/>
    <meta property="og:url" content="{{ url()->current() }}"/>
    <meta property="og:site_name" content="{{ $siteName }}"/>
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')"/>
        <meta property="og:image:alt" content="@yield('title', $siteName)"/>
    @endif

    <!-- Stylesheets -->
    <link rel='stylesheet' href='{{ asset('library-assets/css/main.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/fonts.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/styles.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/font-awesome.min.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/wordpress-main.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/library-custom.css') }}' type='text/css' media='all'/>

    <!-- Dropdown Menu Styles -->
    <style>
        @media screen and (min-width: 768px) {
            /* Position parent menu item */
            header .nav-primary .menu-item-has-children {
                position: relative;
            }

            /* Hide submenu by default */
            header .nav-primary .menu-item-has-children .sub-menu {
                position: absolute;
                top: 100%;
                left: 0;
                display: none;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
                background: white;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                min-width: 220px;
                z-index: 1000;
                margin-top: 0;
                padding: 0.5rem 0;
            }

            /* Show submenu on hover/open */
            header .nav-primary .menu-item-has-children.open .sub-menu,
            header .nav-primary .menu-item-has-children:hover .sub-menu {
                display: block;
                opacity: 1;
                visibility: visible;
            }

            /* Submenu link styles */
            header .nav-primary .sub-menu li a {
                display: block;
                padding: 0.75rem 1.25rem;
                color: #333;
                text-decoration: none;
                white-space: nowrap;
                transition: background 0.2s ease, color 0.2s ease;
            }

            header .nav-primary .sub-menu li a:hover {
                background: #f0f0f0;
                color: #007cba;
            }

            /* Active menu item styles */
            header .nav-primary .menu-item.current-menu-item > a,
            header .nav-primary .menu-item.current-menu-ancestor > a {
                color: #007cba;
                font-weight: 600;
            }

            header .nav-primary .sub-menu .menu-item.current-menu-item > a {
                background: #e6f3f9;
                color: #007cba;
                font-weight: 600;
            }
        }

        /* Search Container Dropdown */
        .menu-search {
            position: relative;
        }

        .menu-search .search-container {
            position: absolute;
            top: 100%;
            right: 0;
            display: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 1rem;
            min-width: 300px;
            z-index: 1000;
            border-radius: 4px;
            margin-top: 0.5rem;
        }

        .menu-search .search-container.active {
            display: block;
            opacity: 1;
            visibility: visible;
        }

        .menu-search .searchform {
            display: flex;
            gap: 0.5rem;
        }

        .menu-search .search-field {
            flex: 1;
            padding: 0.5rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .menu-search .search-field:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.1);
        }

        .menu-search .search-submit {
            padding: 0.5rem 1rem;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .menu-search .search-submit:hover {
            background: #005a87;
        }

        @media screen and (max-width: 767px) {
            .menu-search .search-container {
                right: auto;
                left: 0;
                min-width: 280px;
            }
        }
    </style>

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
                            <button class="toggle-option {{ request()->is('library*') ? 'active' : '' }}" onclick="window.location.href='{{ route('library.index') }}'" data-module="library" title="Login required for full access">
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
                        <li id="menu-item-1362" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1362"><a href="#">About</a>
                            <ul class="sub-menu">
                                <li class="menu-item-disabled"><a href="#" onclick="event.preventDefault();">FSM Language Policy</a></li>
                                <li class="menu-item-disabled"><a href="#" onclick="event.preventDefault();">FSM VLA Standards</a></li>
                                <li class="menu-item-disabled"><a href="#" onclick="event.preventDefault();">Resource library</a></li>
                                <li class="menu-item-disabled"><a href="#" onclick="event.preventDefault();">Photo gallery</a></li>
                            </ul>
                        </li>

                        {{-- Dynamic CMS Pages --}}
                        @if(isset($cmsPages) && $cmsPages->count() > 0)
                            @foreach($cmsPages as $page)
                                @php
                                    $isActive = request()->is($page->slug) || request()->is($page->slug . '/*');
                                    $hasActiveChild = false;
                                    if ($page->children->count() > 0) {
                                        foreach ($page->children as $child) {
                                            if (request()->is($child->slug) || request()->is($child->slug . '/*')) {
                                                $hasActiveChild = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp

                                @if($page->children->count() > 0)
                                    {{-- Page with children (dropdown) --}}
                                    <li class="menu-item menu-item-has-children menu-item-cms menu-item-page-{{ $page->id }} {{ $isActive || $hasActiveChild ? 'current-menu-item current-menu-ancestor' : '' }}">
                                        <a href="{{ route('pages.show', $page->slug) }}">{{ $page->title }}</a>
                                        <ul class="sub-menu">
                                            @foreach($page->children as $childPage)
                                                @php
                                                    $isChildActive = request()->is($childPage->slug) || request()->is($childPage->slug . '/*');
                                                @endphp
                                                <li class="menu-item menu-item-cms-child menu-item-page-{{ $childPage->id }} {{ $isChildActive ? 'current-menu-item' : '' }}">
                                                    <a href="{{ route('pages.show', $childPage->slug) }}">{{ $childPage->title }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @else
                                    {{-- Page without children (single link) --}}
                                    <li class="menu-item menu-item-cms menu-item-page-{{ $page->id }} {{ $isActive ? 'current-menu-item' : '' }}">
                                        <a href="{{ route('pages.show', $page->slug) }}">{{ $page->title }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif

                        @guest
                            <li class="menu-item menu-login"><a href="{{ route('login') }}">Login</a></li>
                        @else
                            <li class="menu-item menu-item-has-children menu-user">
                                <a href="#">{{ Auth::user()->name }}</a>
                                <ul class="sub-menu">
                                    <li><a href="{{ route('bookmarks.index') }}"><i class="fal fa-bookmark"></i> My Bookmarks</a></li>
                                    <li><a href="{{ route('profile.edit') }}">Profile</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                            @csrf
                                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">Logout</a>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>

                <div class="menu-search">
                    <a class="search-toggle" href="#searchform"><i class="fal fa-search" aria-hidden="true"></i><span class="screen-reader-text">Toggle Search</span></a>
                    <div class="search-container" aria-expanded="false">
                        <form role="search" method="get" class="searchform" action="{{ route('library.index') }}">
                            <label for="basic-site-search" id="searchform" class="screen-reader-text">
                                Search for:
                            </label>
                            <input type="search" class="search-field" id="basic-site-search" placeholder="Search books, authors, topics…" value="" name="search">
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
        <p class="tagline">
            Strengthening teaching and learning through the voices and languages of Micronesia.
        </p>
    </div>
</footer>

<!-- Site Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="address-contact-link">
            <address>
                <p class="mb-1"><img class="ndoe-logo" src="{{ asset('library-assets/images/NDOE.png') }}" alt=""></p>
                <p>FSM Department of Education<br>
                    PO Box PS 87<br>
                    Palikir, Pohnpei, FM 96941<br>
                    <a href="https://www.national.doe.fm" style="color: white" target="_blank" rel="noopener noreferrer">www.national.doe.fm</a>
                </p>

            </address>
            <address>
                <p class="mb-1">
                    <img class="irei-logo" src="{{ asset('library-assets/images/iREi.png') }}" alt=""></p>
                <p>Island Research & Education Initiative<br>
                    PO Box PS 303<br>
                    Palikir, Pohnpei, FM 96941<br>
                    <a href="https://www.islandresearch.org" style="color: white" target="_blank" rel="noopener noreferrer">www.islandresearch.org</a>
                </p>

            </address>
            <address>
                <p class="mb-1">
                    <img class="c4gts-logo" src="{{ asset('library-assets/images/C4GTS.png') }}" alt=""></p>
                <p>Center for Getting Things Started<br>
                    472 Inia Lane<br>
                    Hilo, HI 96720<br>
                    <a href="https://www.c4gts.org" style="color: white" target="_blank" rel="noopener noreferrer">www.c4gts.org</a>
                </p>

            </address>
            <ul class="other-links-col other-links-col2">
                <li><span style="color: #999; cursor: default;">Accessibility info</span></li>
                <li><span style="color: #999; cursor: default;">Suggest educational content</span></li>
                <li><span style="color: #999; cursor: default;">Contributor form</span></li>
                <li><span style="color: #999; cursor: default;">Submit resources</span></li>
            </ul>
        </div>
        <div class="logos-coe-links">
            <div class="logos">
                <h3>National Vernacular Language Arts (VLA) curriculum</h3>
                <p><a href="https://micronesian.school/" target="_blank">Resource guide</a></p>
                <p><a href="https://micronesian.school/library" target="_blank">Resource library</a></p>
            </div>
            <div class="links">
                <a class="button button-primary btn-view" stt href="{{ url('/sitemap') }}" title="sitemap"><i aria-hidden="true" class="fal fa-sitemap"></i> <span>Sitemap</span></a>
                @guest
                    <a class="button button-primary btn-view" href="{{ route('login') }}" title="user login"><i aria-hidden="true" class="fal fa-sign-in"></i> <span>Log In</span></a>
                @else
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <a class="button button-primary btn-view" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" title="logout"><i aria-hidden="true" class="fal fa-sign-out"></i> <span>Log Out</span></a>
                    </form>
                @endguest
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

        // Dropdown Menu Hover/Click Behavior
        const menuItemsWithChildren = document.querySelectorAll('.menu-item-has-children');

        menuItemsWithChildren.forEach(function(menuItem) {
            const menuLink = menuItem.querySelector('a');
            const subMenu = menuItem.querySelector('.sub-menu');

            if (menuLink && subMenu) {
                // Desktop: Hover behavior
                menuItem.addEventListener('mouseenter', function() {
                    if (window.innerWidth >= 768) {
                        this.classList.add('open');
                    }
                });

                menuItem.addEventListener('mouseleave', function() {
                    if (window.innerWidth >= 768) {
                        this.classList.remove('open');
                    }
                });

                // Mobile: Click behavior
                menuLink.addEventListener('click', function(e) {
                    if (window.innerWidth < 768 && this.getAttribute('href') === '#') {
                        e.preventDefault();
                        menuItem.classList.toggle('open');
                    }
                });
            }
        });

        // Close all dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.menu-item-has-children')) {
                menuItemsWithChildren.forEach(function(item) {
                    item.classList.remove('open');
                });
            }
        });
    });
</script>

@stack('scripts')

</body>
</html>
