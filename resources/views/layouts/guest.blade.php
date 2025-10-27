<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Micronesian Teachers Digital Library')</title>

        <style>
            /* WordPress custom properties */
            :root {
                --wp--preset--color--coe-green: #009877;
                --wp--preset--color--coe-blue: #007cba;
                --wp--preset--color--white: #ffffff;
                --wp--preset--font-family--proxima-nova: "Proxima Nova", sans-serif;
            }

            /* WordPress login page styling */
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                background: #f1f1f1;
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .login-container {
                background: var(--wp--preset--color--white);
                padding: 26px 24px 46px;
                font-weight: 400;
                overflow: hidden;
                position: relative;
                max-width: 320px;
                width: 100%;
                box-shadow: 0 1px 3px rgba(0,0,0,0.13);
                border-radius: 3px;
            }

            .login-header {
                text-align: center;
                margin-bottom: 25px;
            }

            .login-header h1 {
                font-size: 24px;
                margin: 0 0 25px;
                color: #1d2327;
                font-weight: 600;
                font-family: var(--wp--preset--font-family--proxima-nova);
            }

            .site-logo {
                max-width: 200px;
                margin: 0 auto 25px;
                display: block;
            }

            .login-form {
                margin-top: 20px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            label {
                color: #1d2327;
                font-size: 14px;
                line-height: 1.5;
                display: block;
                margin-bottom: 3px;
                font-weight: 600;
            }

            .form-input {
                background: #fff;
                border: 1px solid #8c8f94;
                border-radius: 3px;
                color: #2c3338;
                font-size: 24px;
                width: 100%;
                border-width: 0.0625rem;
                font-family: Consolas, Monaco, monospace;
                line-height: 1.33333333;
                margin: 0;
                padding: 3px 5px;
                box-sizing: border-box;
                transition: 50ms border-color ease-in-out;
            }

            .form-input:focus {
                border-color: #007cba;
                box-shadow: 0 0 0 1px #007cba;
                outline: 2px solid transparent;
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
                font-size: 13px;
                line-height: 2.15384615;
                min-height: 30px;
                margin: 0;
                padding: 0 10px;
                cursor: pointer;
                border-width: 1px;
                border-style: solid;
                -webkit-appearance: none;
                border-radius: 3px;
                white-space: nowrap;
                box-sizing: border-box;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }

            .button-primary {
                background: #007cba;
                border-color: #007cba;
                color: #fff;
            }

            .button-primary:hover {
                background: #005a87;
                border-color: #005a87;
                color: #fff;
            }

            .button-primary:focus {
                background: #005a87;
                border-color: #005a87;
                color: #fff;
                box-shadow: 0 0 0 1px #007cba;
                outline: 2px solid transparent;
            }

            .button-large {
                min-height: 32px;
                line-height: 2;
                padding: 0 12px;
            }

            .login-submit {
                text-align: left;
                padding: 16px 0 0;
            }

            .login-submit .button {
                float: right;
                margin-left: 8px;
            }

            .login-links {
                margin-top: 16px;
                text-align: center;
            }

            .login-links p {
                margin: 16px 0 0;
                font-size: 13px;
            }

            .login-links a {
                color: #50575e;
                text-decoration: none;
            }

            .login-links a:hover,
            .login-links a:active {
                color: #135e96;
            }

            .back-to-site {
                margin: 16px 0;
                text-align: center;
            }

            .back-to-site a {
                color: #50575e;
                text-decoration: none;
                font-size: 13px;
            }

            .back-to-site a:hover {
                color: #135e96;
            }

            .login-message {
                border-left: 4px solid #00a32a;
                padding: 12px;
                margin-left: 0;
                margin-bottom: 20px;
                background: #fff;
                box-shadow: 0 1px 1px 0 rgba(0,0,0,0.1);
                word-wrap: break-word;
                font-size: 14px;
            }

            .login-error {
                border-left: 4px solid #d63638;
            }

            .demo-credentials {
                background: #fff9e6;
                border: 1px solid #f0c947;
                border-left: 4px solid #f0c947;
                padding: 16px;
                margin-top: 20px;
                border-radius: 3px;
                font-size: 13px;
            }

            .demo-credentials h3 {
                margin: 0 0 12px 0;
                font-size: 14px;
                font-weight: 600;
                color: #1d2327;
            }

            .demo-credentials ul {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .demo-credentials li {
                margin-bottom: 10px;
                padding: 8px;
                background: #fff;
                border-radius: 3px;
                border: 1px solid #e0e0e0;
            }

            .demo-credentials li:last-child {
                margin-bottom: 0;
            }

            .demo-credentials .user-role {
                font-weight: 600;
                color: #1d2327;
                display: block;
                margin-bottom: 4px;
            }

            .demo-credentials .user-email {
                color: #2c3338;
                font-family: Consolas, Monaco, monospace;
                font-size: 12px;
                display: block;
            }

            .demo-credentials .user-password {
                color: #666;
                font-family: Consolas, Monaco, monospace;
                font-size: 12px;
                display: block;
            }

            .demo-credentials .badge {
                display: inline-block;
                padding: 2px 6px;
                font-size: 11px;
                font-weight: 600;
                border-radius: 3px;
                margin-left: 6px;
            }

            .demo-credentials .badge-admin {
                background: #d63638;
                color: #fff;
            }

            .demo-credentials .badge-user {
                background: #007cba;
                color: #fff;
            }

            /* Responsive design */
            @media screen and (max-width: 782px) {
                .login-container {
                    margin: 50px auto;
                    width: auto;
                    max-width: none;
                    box-shadow: none;
                    background: none;
                    padding: 0 20px;
                }
            }
        </style>
    </head>
    <body class="login-page">
        <div class="login-container">
            <div class="login-header">
                <img src="{{ asset('library-assets/images/mtdl-logo.png') }}" alt="Micronesian Teachers Digital Library" class="site-logo">
                <h1>Micronesian Teachers Digital Library</h1>
            </div>

            {{ $slot }}

            <div class="back-to-site">
                <a href="{{ route('library.index') }}">← Go to Micronesian Teachers Digital Library</a>
            </div>
        </div>
    </body>
</html>
