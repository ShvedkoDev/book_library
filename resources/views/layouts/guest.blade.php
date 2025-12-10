<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', $siteName)</title>

        <style>
            /* WordPress custom properties */
            :root {
                --wp--preset--color--coe-green: #009877;
                --wp--preset--color--coe-blue: #007cba;
                --wp--preset--color--white: #ffffff;
                --wp--preset--font-family--proxima-nova: "Proxima Nova", sans-serif;

                /* Modal matching colors */
                --color-primary: #1d496a;
                --color-primary-dark: #007a5e;
                --radius-xl: 12px;
                --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
                --spacing-lg: 1.5rem;
                --font-size-xl: 1.25rem;
            }

            /* WordPress login page styling */
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                background-color: rgba(230, 245, 241, 0.2);
                background-image: url('/library-assets/images/large-topo-bg1_d7935853.png');
                background-position: top;
                background-repeat: repeat;
                background-size: 900px;
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .login-container {
                background: var(--wp--preset--color--white);
                font-weight: 400;
                overflow: hidden;
                position: relative;
                max-width: 450px;
                width: 100%;
                box-shadow: var(--shadow-xl);
                border-radius: var(--radius-xl);
                border: 2px solid var(--color-primary);
            }

            .login-header {
                background: var(--color-primary);
                padding: var(--spacing-lg);
                text-align: center;
            }

            .login-header h1 {
                font-size: var(--font-size-xl);
                margin: 0;
                color: var(--wp--preset--color--white);
                font-weight: 600;
                font-family: var(--wp--preset--font-family--proxima-nova);
            }

            .site-logo {
                max-width: 180px;
                margin: 0 auto 1rem;
                display: block;
                filter: brightness(0) invert(1);
            }

            .login-form {
                padding: 1.5rem;
            }

            .form-group {
                margin-bottom: 1.25rem;
            }

            label {
                color: #1d2327;
                font-size: 0.875rem;
                line-height: 1.5;
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 600;
            }

            .form-input {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 6px;
                color: #2c3338;
                font-size: 1rem;
                width: 100%;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                line-height: 1.5;
                margin: 0;
                padding: 0.75rem 1rem;
                box-sizing: border-box;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }

            .form-input:focus {
                border-color: var(--color-primary);
                box-shadow: 0 0 0 3px rgba(29, 73, 106, 0.1);
                outline: none;
            }

            .checkbox-group {
                margin: 16px 0 16px 0;
            }

            .checkbox-group label {
                font-weight: 400;
                display: flex;
                align-items: center;
                margin-bottom: 0;
                cursor: pointer;
                font-size: 14px;
            }

            .checkbox-group input[type="checkbox"] {
                margin-right: 6px;
                margin-top: 0;
            }

            .button {
                display: inline-block;
                text-decoration: none;
                font-size: 1rem;
                line-height: 1.5;
                margin: 0;
                padding: 0.75rem 1.5rem;
                cursor: pointer;
                border: none;
                border-radius: 6px;
                white-space: nowrap;
                box-sizing: border-box;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                font-weight: 500;
                transition: background-color 0.15s ease-in-out, transform 0.1s ease-in-out;
                width: 100%;
            }

            .button-primary {
                background: var(--color-primary);
                color: #fff;
            }

            .button-primary:hover {
                background: #0f2f46;
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            .button-primary:active {
                transform: translateY(0);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            }

            .button-primary:focus {
                outline: none;
                box-shadow: 0 0 0 3px rgba(29, 73, 106, 0.3);
            }

            .button-large {
                padding: 0.875rem 1.5rem;
                font-size: 1.0625rem;
            }

            .login-submit {
                margin-top: 1.5rem;
            }

            .login-links {
                padding: 0 1.5rem 1.5rem;
                text-align: center;
            }

            .login-links p {
                margin: 0.75rem 0 0;
                font-size: 0.875rem;
            }

            .login-links a {
                color: var(--color-primary);
                text-decoration: none;
                font-weight: 500;
            }

            .login-links a:hover,
            .login-links a:active {
                color: #0f2f46;
                text-decoration: underline;
            }

            .back-to-site {
                padding: 0 1.5rem 1.5rem;
                text-align: center;
                border-top: 1px solid #eee;
                margin-top: 1rem;
                padding-top: 1rem;
            }

            .back-to-site a {
                color: #666;
                text-decoration: none;
                font-size: 0.875rem;
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
            }

            .back-to-site a:hover {
                color: var(--color-primary);
            }

            .login-message {
                border-left: 4px solid #00a32a;
                padding: 1rem;
                margin-bottom: 1.25rem;
                background: #f0f9ff;
                border-radius: 6px;
                word-wrap: break-word;
                font-size: 0.875rem;
                color: #1d2327;
            }

            .login-error {
                border-left: 4px solid #d63638;
                background: #fff5f5;
            }

            .demo-credentials {
                background: #fffbf0;
                border: 1px solid #f0c947;
                border-left: 4px solid #f0c947;
                padding: 1.25rem;
                margin: 1.25rem 1.5rem;
                border-radius: 6px;
                font-size: 0.875rem;
            }

            .demo-credentials h3 {
                margin: 0 0 1rem 0;
                font-size: 1rem;
                font-weight: 600;
                color: #1d2327;
            }

            .demo-credentials ul {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .demo-credentials li {
                margin-bottom: 0.75rem;
                padding: 0.75rem;
                background: #fff;
                border-radius: 6px;
                border: 1px solid #e0e0e0;
                transition: box-shadow 0.15s ease-in-out;
            }

            .demo-credentials li:hover {
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .demo-credentials li:last-child {
                margin-bottom: 0;
            }

            .demo-credentials .user-role {
                font-weight: 600;
                color: #1d2327;
                display: block;
                margin-bottom: 0.5rem;
            }

            .demo-credentials .user-email {
                color: #2c3338;
                font-family: Consolas, Monaco, monospace;
                font-size: 0.8125rem;
                display: block;
                margin-bottom: 0.25rem;
            }

            .demo-credentials .user-password {
                color: #666;
                font-family: Consolas, Monaco, monospace;
                font-size: 0.8125rem;
                display: block;
            }

            .demo-credentials .badge {
                display: inline-block;
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
                font-weight: 600;
                border-radius: 4px;
                margin-left: 0.5rem;
            }

            .demo-credentials .badge-admin {
                background: #d63638;
                color: #fff;
            }

            .demo-credentials .badge-user {
                background: var(--color-primary);
                color: #fff;
            }

            /* Responsive design */
            @media screen and (max-width: 782px) {
                .login-container {
                    margin: 1rem;
                    max-width: 100%;
                }

                .login-header {
                    padding: 1.25rem;
                }

                .login-form,
                .login-links,
                .back-to-site {
                    padding-left: 1.25rem;
                    padding-right: 1.25rem;
                }

                .demo-credentials {
                    margin-left: 1.25rem;
                    margin-right: 1.25rem;
                }
            }
        </style>
    </head>
    <body class="login-page">
        <div class="login-container">
            <div class="login-header">
                <h1>@yield('page_title', 'Log in')</h1>
            </div>

            {{ $slot }}
        </div>
    </body>
</html>
