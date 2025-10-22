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

    <!-- Stylesheets -->
    <link rel='stylesheet' href='{{ asset('library-assets/css/main.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/fonts.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/styles.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/font-awesome.min.css') }}' type='text/css' media='all'/>
    <link rel='stylesheet' href='{{ asset('library-assets/css/wordpress-main.css') }}' type='text/css' media='all'/>

    @stack('styles')
</head>

<body class="page-template page-template-page-landing page-template-page-landing-php page page-id-8">

<div id="wrapper">
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container-fluid">
            <div class="logos-container">
                <a href="{{ url('/') }}" class="logo-link">
                    <img src="https://picsum.photos/200/80?random=3DL" alt="Micronesian Teachers Digital Library" class="site-logo">
                </a>
                <div class="partner-logos">
                    <img src="https://picsum.photos/120/60?random=org1" alt="Partner Organization 1" class="partner-logo">
                    <img src="https://picsum.photos/120/60?random=org2" alt="Partner Organization 2" class="partner-logo">
                </div>
            </div>
        </div>
    </div>

    <!-- Main Menu -->
    <nav class="main-menu library-active" role="navigation" aria-label="main navigation">
        <div class="container-fluid">
            <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <i class="fal fa-bars"></i>
            </div>
            <ul class="menu-items" id="mainMenu">
                <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Guide</a></li>
                <li><a href="{{ route('library.index') }}" class="{{ request()->is('library*') ? 'active' : '' }}">Library</a></li>
                @auth
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                Logout
                            </a>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Login</a></li>
                @endauth
            </ul>
            <div class="menu-right">
                <div class="language-selector">
                    <i class="fal fa-globe"></i>
                    <select id="language-select" onchange="changeLanguage(this.value)">
                        <option value="en">English</option>
                        <option value="ch">Chuukese</option>
                        <option value="po">Pohnpeian</option>
                        <option value="ya">Yapese</option>
                        <option value="ko">Kosraean</option>
                        <option value="mh">Marshallese</option>
                    </select>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content library-layout">
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
            <div class="coe-lockup">
                <a href="https://www.hawaii.edu" target="_blank">
                    <img src="https://picsum.photos/150/60?random=uh" alt="University of Hawaii System" class="coe-logo">
                </a>
            </div>
            <ul class="other-links-col coe-other-links">
                <li><a href="#" target="_blank">Privacy Policy</a></li>
                <li><a href="#" target="_blank">Terms of Use</a></li>
                <li><a href="#" target="_blank">Contact Us</a></li>
            </ul>
        </div>
    </div>
    <div class="uh-footer" style="background-color: #024731; color: #fff; text-align: center; padding: 10px 0;">
        <p>&copy; {{ date('Y') }} Micronesian Teachers Digital Library. All rights reserved.</p>
    </div>
</footer>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mainMenu');
        menu.classList.toggle('active');
    }

    function changeLanguage(lang) {
        console.log('Language changed to:', lang);
        // Language switching logic to be implemented
    }
</script>

@stack('scripts')

</body>
</html>
