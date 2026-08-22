<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Features the site does not use, switched off for this document and for
     * anything it embeds.
     */
    protected const PERMISSIONS_POLICY = 'accelerometer=(), autoplay=(), browsing-topics=(), camera=(), '
        .'display-capture=(), encrypted-media=(), geolocation=(), gyroscope=(), interest-cohort=(), '
        .'magnetometer=(), microphone=(), midi=(), payment=(), usb=(), xr-spatial-tracking=()';

    /**
     * Content Security Policy directives added on top of whatever policy is
     * already present. These are the directives that cannot break us.
     *
     * There is deliberately NO script-src and NO style-src here. Livewire,
     * Alpine and Filament all emit inline scripts and inline styles, and the
     * library PDF viewer loads PDF.js from a CDN, so either directive would
     * break the admin panel and the library UI. Do not add them without first
     * moving those assets behind a nonce.
     */
    protected const CSP_DIRECTIVES = [
        "object-src 'none'",
        "base-uri 'self'",
        "frame-ancestors 'self'",
    ];

    /**
     * Only meaningful on an HTTPS origin. Applied over plain HTTP it would
     * rewrite every same-origin subresource to https:// and break local and
     * LAN dev hosts that have no certificate, so it is added conditionally.
     */
    protected const CSP_SECURE_DIRECTIVES = [
        'upgrade-insecure-requests',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->removePoweredBy($response);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', self::PERMISSIONS_POLICY);
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy($request, $response));

        $this->applyStrictTransportSecurity($request, $response);

        return $response;
    }

    /**
     * Stop advertising the PHP version.
     *
     * Under PHP-FPM the header is emitted by PHP itself and never reaches the
     * response bag, so header_remove() is the only thing that clears it; under
     * LiteSpeed it can be carried on the response object instead. Both paths
     * are cleared here.
     */
    protected function removePoweredBy(Response $response): void
    {
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        $response->headers->remove('X-Powered-By');
    }

    /**
     * Merge our directives into any policy the response already carries so an
     * existing policy is extended rather than thrown away.
     */
    protected function contentSecurityPolicy(Request $request, Response $response): string
    {
        $existing = (string) $response->headers->get('Content-Security-Policy', '');

        $directives = array_values(array_filter(array_map('trim', explode(';', $existing)), 'strlen'));

        $present = array_map(fn (string $directive): string => $this->directiveName($directive), $directives);

        $wanted = self::CSP_DIRECTIVES;

        if ($request->secure()) {
            $wanted = array_merge($wanted, self::CSP_SECURE_DIRECTIVES);
        }

        foreach ($wanted as $directive) {
            if (in_array($this->directiveName($directive), $present, true)) {
                continue;
            }

            $directives[] = $directive;
        }

        return implode('; ', $directives);
    }

    /**
     * The name of a CSP directive, e.g. "object-src 'none'" becomes "object-src".
     */
    protected function directiveName(string $directive): string
    {
        return strtolower(explode(' ', trim($directive), 2)[0]);
    }

    /**
     * Send Strict-Transport-Security only when it has been explicitly turned on.
     *
     * OFF BY DEFAULT ON PURPOSE. Our teachers browse from school and government
     * networks whose firewalls intercept and re-sign TLS. A browser that has
     * seen this header pins the host, and the next time it meets the firewall's
     * substituted certificate it shows a hard certificate error with no
     * "Proceed anyway" option - locking the user out of the site completely
     * until the pin expires. Turning this on prematurely would make the current
     * customer incident considerably worse.
     *
     * Enable it only once the domain is properly categorised by the major web
     * filters so inspecting proxies pass it through untouched. See the notes in
     * config/security.php. 'preload' is never sent, and includeSubDomains is
     * its own flag and also defaults to off.
     */
    protected function applyStrictTransportSecurity(Request $request, Response $response): void
    {
        if (! config('security.hsts.enabled', false)) {
            return;
        }

        // Never advertise the pin over plain HTTP - the browser ignores it and
        // it only risks pinning a host that is not fully on HTTPS yet.
        if (! $request->secure()) {
            return;
        }

        $value = 'max-age='.(int) config('security.hsts.max_age', 31536000);

        if (config('security.hsts.include_subdomains', false)) {
            $value .= '; includeSubDomains';
        }

        $response->headers->set('Strict-Transport-Security', $value);
    }
}
