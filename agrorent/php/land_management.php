<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// This action is public for farmers to see all lands
if ($action === 'list_all_lands') {
    listAllLands();
    exit;
}

// All other actions require a logged-in user
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied. Please log in.']);
    exit;
}

switch ($action) {
    case 'list_my_lands':
        listMyLands();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid land action.']);
        break;
}

function listAllLands() {
    global $conn;
    $result = $conn->query("SELECT * FROM lands WHERE status = 'available'");
    $lands = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $lands[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'lands' => $lands]);
}

function listMyLands() {
    global $conn;
    if (!in_array('owner', $_SESSION['user']['roles'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'You do not have owner privileges.']);
        return;
    }
    
    $owner_id = $_SESSION['user']['id'];
    $stmt = $conn->prepare("SELECT * FROM lands WHERE owner_id = ?");
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lands = [];
    while ($row = $result->fetch_assoc()) {
        $lands[] = $row;
    }
    echo json_encode(['status' => 'success', 'lands' => $lands]);
    $stmt->close();
}

$conn->close();
?>