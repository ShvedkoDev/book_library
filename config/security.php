<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security (HSTS)
    |--------------------------------------------------------------------------
    |
    | HSTS is DISABLED BY DEFAULT and must stay that way until the trade-off
    | below has been reviewed for this audience.
    |
    | Most of our teachers reach the site from school and government networks
    | that terminate and re-sign TLS on an inspecting firewall. Once a browser
    | has seen a Strict-Transport-Security header it pins the host: the next
    | time it meets the firewall's substituted certificate it shows a HARD
    | failure with NO "Proceed anyway" escape hatch, and the user is locked out
    | of the site entirely until the pin expires. That would turn the current
    | customer incident into a total outage for exactly the people we are
    | trying to serve.
    |
    | Only switch SECURITY_HSTS_ENABLED=true once:
    |   1. the domain has been categorised as Education by the major web
    |      filters, so inspecting proxies bypass it instead of re-signing it,
    |   2. the customer has confirmed no school network is intercepting it, and
    |   3. a short max-age (e.g. 300) has been trialled before the full year.
    |
    | 'preload' is intentionally not offered here: submission to the browser
    | preload list is effectively irreversible.
    |
    */

    'hsts' => [

        // Master switch. Even when true the header is only sent over HTTPS.
        'enabled' => env('SECURITY_HSTS_ENABLED', false),

        // Lifetime of the pin, in seconds. Defaults to one year.
        'max_age' => env('SECURITY_HSTS_MAX_AGE', 31536000),

        // Extends the pin to every subdomain. Off by default: a subdomain that
        // is not yet on HTTPS would become unreachable.
        'include_subdomains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', false),

    ],

];
