<?php
// Simple test to verify data storage functionality
echo "<h2>Practice 9 Data Storage Test</h2>";

// Test 1: Check if data directory exists and is writable
$dataDir = __DIR__ . '/data';
echo "<h3>Test 1: Data Directory</h3>";
echo "Data directory path: " . $dataDir . "<br>";
echo "Directory exists: " . (is_dir($dataDir) ? "✅ YES" : "❌ NO") . "<br>";
echo "Directory writable: " . (is_writable($dataDir) ? "✅ YES" : "❌ NO") . "<br>";

// Test 2: Try to create a test file
echo "<h3>Test 2: File Write Test</h3>";
$testFile = $dataDir . '/test.txt';
$testContent = "Test data written at: " . date('Y-m-d H:i:s') . "\n";

if (file_put_contents($testFile, $testContent) !== false) {
    echo "✅ Successfully wrote test file<br>";
    echo "File path: " . $testFile . "<br>";
    echo "Content: " . htmlspecialchars($testContent) . "<br>";
    
    // Clean up test file
    unlink($testFile);
    echo "✅ Test file cleaned up<br>";
} else {
    echo "❌ Failed to write test file<br>";
}

// Test 3: Check existing data files
echo "<h3>Test 3: Existing Data Files</h3>";
$files = glob($dataDir . '/*');
if (empty($files)) {
    echo "No data files found yet. This is expected for a fresh installation.<br>";
} else {
    echo "Found " . count($files) . " file(s):<br>";
    foreach ($files as $file) {
        echo "- " . basename($file) . " (" . filesize($file) . " bytes)<br>";
    }
}

// Test 4: Check form processing
echo "<h3>Test 4: Test Form Submission</h3>";
echo '<form action="process_form.php" method="POST" style="background: #f0f0f0; padding: 20px; margin: 20px 0; border-radius: 8px;">';
echo '<h4>Quick Test Form</h4>';
echo '<label>Full Name: <input type="text" name="full_name" value="Test User" required></label><br><br>';
echo '<label>First Name: <input type="text" name="first_name" value="Test" required></label><br><br>';
echo '<label>Last Name: <input type="text" name="last_name" value="User" required></label><br><br>';
echo '<label>Email: <input type="email" name="email" value="test@example.com" required></label><br><br>';
echo '<label>Phone: <input type="tel" name="phone" value="1234567890"></label><br><br>';
echo '<label>Comments: <textarea name="comments">This is a test submission to verify data storage functionality.</textarea></label><br><br>';
echo '<input type="submit" value="Submit Test Data" style="background: #0046FF; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">';
echo '</form>';

echo "<hr>";
echo '<p><a href="index.php">← Back to Main Form</a> | <a href="view_data.php">View Stored Data</a></p>';
?>