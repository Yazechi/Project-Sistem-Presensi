<?php
/**
 * Script to generate demo users with properly hashed passwords
 * Run this script once to create demo accounts
 */

require_once __DIR__ . '/../config/database.php';

// Demo user data
$demo_users = [
    [
        'username' => 'admin',
        'password' => 'admin123',
        'nama' => 'Administrator',
        'role' => 'admin'
    ],
    [
        'username' => '198001012005011001',
        'password' => 'dosen123',
        'nama' => 'Dr. Ahmad Budiman',
        'role' => 'dosen'
    ],
    [
        'username' => '210001001',
        'password' => 'mhs123',
        'nama' => 'Budi Santoso',
        'role' => 'mahasiswa'
    ]
];

try {
    $conn = getDBConnection();
    
    echo "Creating demo users...\n";
    
    foreach ($demo_users as $user) {
        // Hash password using PASSWORD_BCRYPT
        $hashed_password = password_hash($user['password'], PASSWORD_BCRYPT);
        
        // Check if user already exists
        $check_query = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $user['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "User {$user['username']} already exists, updating password...\n";
            $update_query = "UPDATE users SET password = ?, nama = ?, role = ? WHERE username = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssss", $hashed_password, $user['nama'], $user['role'], $user['username']);
            $stmt->execute();
        } else {
            echo "Creating user {$user['username']}...\n";
            $insert_query = "INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssss", $user['username'], $hashed_password, $user['nama'], $user['role']);
            $stmt->execute();
        }
        
        echo "✓ User {$user['username']} ({$user['role']}) created successfully\n";
    }
    
    echo "\nDemo users created successfully!\n";
    echo "\nLogin credentials:\n";
    echo "Admin: admin / admin123\n";
    echo "Dosen: 198001012005011001 / dosen123\n";
    echo "Mahasiswa: 210001001 / mhs123\n";
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
