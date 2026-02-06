<?php
/**
 * output_escaping.php - Output Escaping Helper Functions
 * 
 * Centralized output escaping to ensure consistent XSS prevention
 * Use these functions for all user-supplied data output
 */

/**
 * Escape HTML special characters for safe display in HTML context
 * 
 * @param string $text Text to escape
 * @param int $flags ENT_* flags (default: ENT_QUOTES | ENT_HTML5)
 * @param string $encoding Character encoding (default: UTF-8)
 * @return string Escaped text
 */
function esc_html($text, $flags = null, $encoding = 'UTF-8') {
    if ($flags === null) {
        $flags = ENT_QUOTES | ENT_HTML5;
    }
    return htmlspecialchars($text, $flags, $encoding);
}

/**
 * Escape text for use in HTML attributes
 * 
 * @param string $text Text to escape
 * @return string Escaped text
 */
function esc_attr($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Escape text for use in JavaScript context
 * 
 * @param string $text Text to escape
 * @return string Escaped text
 */
function esc_js($text) {
    // Escape for JavaScript string context
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace('"', '\\"', $text);
    $text = str_replace("'", "\\'", $text);
    $text = str_replace("\n", '\\n', $text);
    $text = str_replace("\r", '\\r', $text);
    $text = str_replace("\t", '\\t', $text);
    return $text;
}

/**
 * Escape text for use in URL query string
 * 
 * @param string $text Text to escape
 * @return string URL-encoded text
 */
function esc_url_param($text) {
    return urlencode($text);
}

/**
 * Escape text for use in CSS context
 * 
 * @param string $text Text to escape
 * @return string Escaped text
 */
function esc_css($text) {
    return str_replace(['"', "'", '\\'], ['\"', "\'", '\\\\'], $text);
}

/**
 * Safe echo - escapes HTML and outputs
 * Shorthand for: echo esc_html($text)
 * 
 * @param string $text Text to output
 * @param int $flags Optional escaping flags
 */
function e($text, $flags = null) {
    echo esc_html($text, $flags);
}

/**
 * Escape JSON data for use in JavaScript
 * 
 * @param mixed $data Data to encode
 * @return string JSON-encoded string
 */
function esc_json($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Format and escape date/time output
 * 
 * @param string $datetime DateTime string
 * @param string $format Output format
 * @return string Formatted and escaped datetime
 */
function esc_datetime($datetime, $format = 'Y-m-d H:i:s') {
    try {
        $date = new DateTime($datetime);
        return esc_html($date->format($format));
    } catch (Exception $e) {
        return esc_html($datetime);
    }
}

/**
 * Safe array access with escaping
 * Returns escaped value or default
 * 
 * @param array $array Array to access
 * @param string $key Array key
 * @param mixed $default Default value if key doesn't exist
 * @return string Escaped value
 */
function esc_array($array, $key, $default = '-') {
    if (isset($array[$key]) && $array[$key] !== null) {
        return esc_html($array[$key]);
    }
    return esc_html($default);
}

?>
