<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Access denied.']));
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user']['id'];

switch ($action) {
    case 'add_owner_role':
        addOwnerRole($user_id);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid user action.']);
        break;
}

function addOwnerRole($user_id) {
    global $conn;

    // First, check if the user already has the owner role (role_id = 2)
    $stmt_check = $conn->prepare("SELECT id FROM user_roles WHERE user_id = ? AND role_id = 2");
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        // User is already an owner, no action needed
        echo json_encode(['status' => 'success', 'message' => 'Owner role already exists.']);
        $stmt_check->close();
        return;
    }
    $stmt_check->close();

    // If not, insert the owner role
    $stmt_insert = $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, 2)");
    $stmt_insert->bind_param("i", $user_id);
    if ($stmt_insert->execute()) {
        // Update the session to include the new role
        $_SESSION['user']['roles'][] = 'owner';
        echo json_encode(['status' => 'success', 'message' => 'You now have landowner privileges!', 'user' => $_SESSION['user']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update your role.']);
    }
    $stmt_insert->close();
}

$conn->close();
?>