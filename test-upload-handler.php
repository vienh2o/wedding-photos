<?php
/**
 * Test script to verify upload handler returns clean JSON
 */

echo "=== Testing Upload Handler (uploadFile.php) ===\n\n";

// Test 1: Check if upload handler exists
echo "1. Upload Handler Check:\n";
if (file_exists('uploadFile.php')) {
    echo "   ✓ uploadFile.php exists\n";
} else {
    echo "   ✗ uploadFile.php missing\n";
    exit;
}

// Test 2: Test GET request (should return method not allowed)
echo "\n2. Testing GET request:\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json'
    ]
]);

$response = file_get_contents('http://localhost:8001/uploadFile.php', false, $context);
echo "   Response: " . substr($response, 0, 100) . "...\n";

// Test 3: Test POST request without file
echo "\n3. Testing POST request without file:\n";
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json'
    ]
]);

$response = file_get_contents('http://localhost:8001/uploadFile.php', false, $context);
echo "   Response: " . substr($response, 0, 100) . "...\n";

// Test 4: Check if response is valid JSON
echo "\n4. JSON Validation:\n";
$jsonData = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "   ✓ Response is valid JSON\n";
    echo "   ✓ Success: " . ($jsonData['success'] ? 'true' : 'false') . "\n";
    if (isset($jsonData['error'])) {
        echo "   ✓ Error message: " . $jsonData['error'] . "\n";
    }
} else {
    echo "   ✗ Invalid JSON: " . json_last_error_msg() . "\n";
    echo "   Raw response: " . $response . "\n";
}

echo "\n=== Test Complete ===\n"; 