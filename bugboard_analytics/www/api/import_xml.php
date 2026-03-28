<?php
/**
 * /api/import_xml.php
 *
 * Parses an incoming XML bug-report and returns a JSON preview.
 *
 * INTENTIONAL VULNERABILITY: XXE (CWE-611)
 *   - libxml_disable_entity_loader(false) re-enables external entity loading.
 *   - LIBXML_NOENT causes the parser to substitute &entity; references.
 *   - An attacker can use a SYSTEM entity pointing to a local file and have
 *     its contents reflected back in the "parsedDesc" field.
 *
 * Example exploit payload:
 *   <?xml version="1.0"?>
 *   <!DOCTYPE bugreport [
 *     <!ENTITY xxe SYSTEM "file:///var/www/html/flag_xxe.txt">
 *   ]>
 *   <bugreport>
 *     <title>Test XXE</title>
 *     <severity>low</severity>
 *     <description>&xxe;</description>
 *   </bugreport>
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$xml_input = file_get_contents('php://input');
if (empty(trim($xml_input))) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty body']);
    exit;
}

// ──────────────────────────────────────────────
//  VULNERABLE XML PARSING – do NOT deploy in prod
// ──────────────────────────────────────────────
// Suppress the deprecation notice on PHP 8+ so it doesn't break the JSON output
@libxml_disable_entity_loader(false);

$prev = libxml_use_internal_errors(true);
$dom  = new DOMDocument();
$ok   = $dom->loadXML($xml_input, LIBXML_NOENT | LIBXML_DTDLOAD);
libxml_use_internal_errors($prev);

if (!$ok) {
    http_response_code(422);
    echo json_encode(['error' => 'XML parse error', 'details' => libxml_get_last_error() ? libxml_get_last_error()->message : 'unknown']);
    exit;
}

// Extract fields
$get = function (DOMDocument $d, string $tag): string {
    $nodes = $d->getElementsByTagName($tag);
    return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
};

$title       = $get($dom, 'title');
$severity    = $get($dom, 'severity');
$description = $get($dom, 'description');
$target      = $get($dom, 'target');

if ($title === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Missing required element: <title>']);
    exit;
}

echo json_encode([
    'status'      => 'ok',
    'parsedTitle' => $title,
    'parsedSev'   => $severity,
    'parsedDesc'  => substr($description, 0, 120),   // first 120 chars reflected
    'parsedTarget'=> $target,
]);
