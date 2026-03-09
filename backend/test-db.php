<?php
/**
 * DATABASE CONNECTION TESTER
 * Save this as: backend/test-db.php
 * Open in browser: http://localhost:8000/test-db.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";

// Test 1: Check if MySQL extension is loaded
echo "<h2>Test 1: MySQL Extension</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "<p class='success'>✅ PDO MySQL extension is loaded</p>";
} else {
    echo "<p class='error'>❌ PDO MySQL extension is NOT loaded</p>";
    echo "<p>You need to enable pdo_mysql in php.ini</p>";
    exit;
}

// Test 2: Try to connect
echo "<h2>Test 2: Database Connection</h2>";

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'job_portal';

echo "<p class='info'>Trying to connect with:</p>";
echo "<ul>";
echo "<li>Host: <b>$host</b></li>";
echo "<li>User: <b>$user</b></li>";
echo "<li>Password: <b>" . (empty($pass) ? "(empty)" : "***") . "</b></li>";
echo "<li>Database: <b>$dbname</b></li>";
echo "</ul>";

try {
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Connected to MySQL server successfully!</p>";
    
    // Test 3: Check if database exists
    echo "<h2>Test 3: Database Check</h2>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ Database '$dbname' exists</p>";
        
        // Test 4: Connect to specific database
        echo "<h2>Test 4: Connect to Database</h2>";
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
        echo "<p class='success'>✅ Connected to database '$dbname'</p>";
        
        // Test 5: Check tables
        echo "<h2>Test 5: Tables Check</h2>";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p class='success'>✅ Found " . count($tables) . " tables:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
            
            // Test 6: Check admin user
            echo "<h2>Test 6: Admin User Check</h2>";
            if (in_array('users', $tables)) {
                $stmt = $pdo->query("SELECT email, user_type FROM users LIMIT 5");
                $users = $stmt->fetchAll();
                
                if (count($users) > 0) {
                    echo "<p class='success'>✅ Found " . count($users) . " users:</p>";
                    echo "<ul>";
                    foreach ($users as $user) {
                        echo "<li>{$user['email']} ({$user['user_type']})</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='error'>❌ No users found in database</p>";
                    echo "<p>Run the schema.sql file to create the admin user</p>";
                }
            }
            
        } else {
            echo "<p class='error'>❌ No tables found in database</p>";
            echo "<p>You need to import schema.sql</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Database '$dbname' does NOT exist</p>";
        echo "<p><b>Solution:</b></p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a></li>";
        echo "<li>Click 'New' to create a database</li>";
        echo "<li>Name it: <b>job_portal</b></li>";
        echo "<li>Collation: <b>utf8mb4_unicode_ci</b></li>";
        echo "<li>Click 'Create'</li>";
        echo "<li>Then import your schema.sql file</li>";
        echo "</ol>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Connection failed: " . $e->getMessage() . "</p>";
    echo "<p><b>Common solutions:</b></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP/WAMP MySQL is running</li>";
    echo "<li>Check your credentials in database.php</li>";
    echo "<li>Try using '127.0.0.1' instead of 'localhost'</li>";
    echo "<li>Check if MySQL is running on a different port (3307, 3308)</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If all tests passed, your database is ready!</p>";
echo "<p>If not, follow the solutions above.</p>";
?>