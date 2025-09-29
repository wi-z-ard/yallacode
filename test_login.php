<?php
/**
 * Test Login and Session
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

echo "<h2>Login and Session Test</h2>";

// Check if already logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<p style='color: green;'>✅ Already logged in as: " . $user['name'] . "</p>";
    echo "<p><a href='index.php'>Go to Dashboard</a></p>";
    echo "<p><a href='api/auth.php?action=logout'>Logout</a></p>";
} else {
    echo "<p style='color: orange;'>⚠️ Not logged in</p>";
    
    // Try to login automatically with admin credentials
    if (isset($_GET['auto_login'])) {
        echo "<h3>Attempting Auto Login...</h3>";
        
        if (login('admin@expenses.com', 'admin123')) {
            echo "<p style='color: green;'>✅ Auto login successful!</p>";
            echo "<script>window.location.reload();</script>";
        } else {
            echo "<p style='color: red;'>❌ Auto login failed. Check admin credentials.</p>";
            echo "<p><a href='reset_admin.php'>Reset Admin Password</a></p>";
        }
    } else {
        echo "<p><a href='?auto_login=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Auto Login</a></p>";
        echo "<p><a href='login.php'>Manual Login</a></p>";
    }
}

// Session info
echo "<h3>Session Information:</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p>User ID in Session: " . $_SESSION['user_id'] . "</p>";
    echo "<p>User Name in Session: " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
    echo "<p>User Role in Session: " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";
}

// Test API call
if (isLoggedIn()) {
    echo "<h3>Test API Call:</h3>";
    echo "<button onclick='testAPI()' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Categories API</button>";
    echo "<div id='api-result' style='margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 5px; display: none;'></div>";
}

?>

<script>
async function testAPI() {
    const resultDiv = document.getElementById('api-result');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = 'Testing API...';
    
    try {
        const response = await fetch('api/categories.php');
        const data = await response.json();
        
        if (response.ok) {
            resultDiv.innerHTML = '<p style="color: green;">✅ API Success!</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
        } else {
            resultDiv.innerHTML = '<p style="color: red;">❌ API Error (' + response.status + '):</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
        }
    } catch (error) {
        resultDiv.innerHTML = '<p style="color: red;">❌ Network Error:</p><pre>' + error.message + '</pre>';
    }
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; max-height: 300px; overflow-y: auto; }
p { margin: 5px 0; }
</style>
