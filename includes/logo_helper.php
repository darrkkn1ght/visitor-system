<?php
// includes/logo_helper.php

if (!function_exists('getLogoHref')) {
    function getLogoHref()
    {
        // reuse the same session logic as admin_dashboard.php
        if (session_status() === PHP_SESSION_NONE) {
            // We shouldn't start a session just to check, but usually pages already start it.
            // If strictly needed, we can start it, but it might have side effects.
            // Safe approach: check if $_SESSION is set.
        }

        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            return 'admin_dashboard.php';
        }
        return 'index.php';
    }
}
?>