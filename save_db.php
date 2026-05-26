<?php
/**
 * save_db.php — Macro's Kitchen
 * Receives JSON from admin.html and writes it to db.json
 * Place this file in the same directory as db.json and admin.html
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: same-origin');
header('Access-Control-Allow-Methods: POST');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Read raw JSON body
$body = file_get_contents('php://input');
if (!$body) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Empty body']);
    exit;
}

// Validate JSON
$data = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

// Basic structure validation
if (!isset($data['restaurant']) || !isset($data['sections'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$dbPath = __DIR__ . '/db.json';

// Backup current db.json before overwriting
$backupPath = __DIR__ . '/db_backup_' . date('YmdHis') . '.json';
if (file_exists($dbPath)) {
    copy($dbPath, $backupPath);
}

// Write new db.json
$written = file_put_contents($dbPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($written === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not write db.json — check file permissions']);
    exit;
}

echo json_encode([
    'success'  => true,
    'message'  => 'Saved successfully',
    'bytes'    => $written,
    'backup'   => basename($backupPath)
]);
