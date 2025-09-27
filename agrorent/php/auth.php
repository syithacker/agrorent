<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register': registerUser(); break;
    case 'login': loginUser(); break;
    case 'logout': logoutUser(); break;
    default: echo json_encode(['status' => 'error', 'message' => 'Invalid action']); break;
}

function registerUser() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name']; $email = $data['email']; $password = $data['password']; $phone = $data['phone']; $address = $data['address']; $roles = $data['roles'];
    
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email); $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows > 0) { echo json_encode(['status' => 'error', 'message' => 'Email already exists.']); $stmt->close(); return; }
    $stmt->close();
    
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $hashed_password, $phone, $address);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id; $stmt->close();
        
        $stmt_roles = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ?");
        $stmt_insert = $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        
        foreach ($roles as $role_name) {
            $stmt_roles->bind_param("s", $role_name); $stmt_roles->execute(); $result = $stmt_roles->get_result();
            if ($row = $result->fetch_assoc()) { 
                $stmt_insert->bind_param("ii", $user_id, $row['role_id']); 
                $stmt_insert->execute(); 
            }
        }
        $stmt_roles->close(); $stmt_insert->close();
        echo json_encode(['status' => 'success', 'message' => 'Registration successful.']);
    } else { 
        echo json_encode(['status' => 'error', 'message' => 'Registration failed.']); 
    }
}

function loginUser() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email']; $password = $data['password'];
    
    $stmt = $conn->prepare("SELECT user_id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email); $stmt->execute(); $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $stmt_roles = $conn->prepare("SELECT r.role_name FROM roles r JOIN user_roles ur ON r.role_id = ur.role_id WHERE ur.user_id = ?");
            $stmt_roles->bind_param("i", $user['user_id']); $stmt_roles->execute();
            $roles_result = $stmt_roles->get_result(); $roles = [];
            while ($row = $roles_result->fetch_assoc()) { $roles[] = $row['role_name']; }
            $stmt_roles->close();
            
            $_SESSION['user'] = ['id' => $user['user_id'], 'name' => $user['name'], 'email' => $user['email'], 'roles' => $roles];
            echo json_encode(['status' => 'success', 'message' => 'Login successful', 'user' => $_SESSION['user']]);
        } else { 
            echo json_encode(['status' => 'error', 'message' => 'Invalid password.']); 
        }
    } else { 
        echo json_encode(['status' => 'error', 'message' => 'User not found.']); 
    }
    $stmt->close();
}

function logoutUser() {
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
}

$conn->close();
?>