<?php
/**
 * Security Headers & Session Configuration
 * Include this file at the very beginning of all protected pages
 * Must be called BEFORE session_start() for proper effect
 * 
 * Security Features:
 * - Secure session cookies (HTTPS only, HTTP-only, SameSite=Strict)
 * - HTTPS enforcement with HSTS header
 * - Security headers (X-Frame-Options, X-Content-Type-Options, CSP)
 * - Session timeout protection
 */

// ============================================================
// 1. HTTPS ENFORCEMENT & HSTS
// ============================================================
// Redirect to HTTPS if not already (allow localhost for development)
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    // Allow development on localhost without HTTPS redirect
    $is_localhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost']);

    if (!$is_localhost && $_SERVER['HTTP_HOST'] !== 'localhost') {
        $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirect_url, true, 301);
        exit;
    }
}

// ============================================================
// 2. SESSION CONFIGURATION (Must be set BEFORE session_start())
// ============================================================
// Only set if not already configured
if (ini_get('session.cookie_secure') === '0') {
    ini_set('session.cookie_secure', '1');      // HTTPS only
}
if (ini_get('session.cookie_httponly') === '0') {
    ini_set('session.cookie_httponly', '1');    // No JavaScript access
}
if (ini_get('session.use_strict_mode') === '0') {
    ini_set('session.use_strict_mode', '1');    // Reject uninitialized session IDs
}
if (ini_get('session.gc_maxlifetime') !== '1800') {
    ini_set('session.gc_maxlifetime', '1800');  // 30-minute timeout
}

// SameSite attribute (PHP 7.3+)
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
} else {
    // Fallback for older PHP versions
    session_set_cookie_params(
        1800,           // lifetime
        '/',            // path
        '',             // domain
        true,           // secure (HTTPS only)
        true            // httponly (no JavaScript access)
    );
}

// ============================================================
// 3. SECURITY HEADERS (Set after session config)
// ============================================================
// Only add if not already sent
if (!headers_sent()) {
    // HSTS: Force HTTPS for 1 year, including subdomains
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');

    // Clickjacking protection
    header('X-Frame-Options: DENY');

    // MIME-sniffing protection
    header('X-Content-Type-Options: nosniff');

    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    header("Content-Security-Policy: default-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com data:; connect-src 'self' ws: wss:; form-action 'self'; base-uri 'self'; frame-ancestors 'none'; manifest-src 'self';");

    // CSP Report-Only for development (helps identify violations without blocking)
    // header("Content-Security-Policy-Report-Only: default-src 'none'; script-src 'self'; style-src 'self' https://fonts.googleapis.com; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com data:; connect-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'; manifest-src 'self'; report-uri /csp-report.php;");

    // Permissions policy (formerly Feature-Policy)
    header("Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()");
}

// ============================================================
// 4. SESSION TIMEOUT WARNING (Optional)
// ============================================================
// You can use this to warn users about impending session timeout
$session_timeout = 1800; // 30 minutes
$warning_threshold = 300; // Warn at 5 minutes remaining

// This can be checked in your protected pages:
// if (isset($_SESSION['login_time'])) {
//     $elapsed = time() - $_SESSION['login_time'];
//     if ($elapsed > ($session_timeout - $warning_threshold)) {
//         // Show warning to user
//     }
// }

// ============================================================
// USAGE INSTRUCTIONS:
// ============================================================
// 1. Add this at the VERY BEGINNING of admin pages (BEFORE session_start()):
//    require_once 'security_headers.php';
//    session_start();
//
// 2. Pages that need this:
//    - admin_login.php (already has session_start(), add before it)
//    - admin_dashboard.php (already has session_start(), add before it)
//    - admin_auth.php (already has session_start(), add before it)
//    - events_calendar.php (already has session_start(), add before it)
//    - checkout.php (already has session_start(), add before it)
//    - create_event.php (if exists, already has session_start(), add before it)
//    - destinations.php (if exists, already has session_start(), add before it)
//    - backup_records.php (if exists, already has session_start(), add before it)
//
// 3. Development note: If testing without HTTPS, localhost is allowed
//    For production, HTTPS is required and enforced.
