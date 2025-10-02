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
    case 'request_to_rent':
        requestToRent($user_id);
        break;
    case 'get_my_requests':
        getMyRequests($user_id);
        break;
    case 'get_my_applications':
        getMyApplications($user_id);
        break;
    case 'handle_request':
        handleRequest($user_id);
        break;
    case 'get_unseen_notifications':
        getUnseenNotifications($user_id);
        break;
    case 'mark_notification_as_seen':
        markNotificationAsSeen($user_id);
        break;
    case 'get_all_pending_requests':
        getAllPendingRequests($user_id);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid booking action.']);
        break;
}

function requestToRent($farmer_id) {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $land_id = $data['land_id'];
    $stmt = $conn->prepare("SELECT owner_id FROM lands WHERE land_id = ?");
    $stmt->bind_param("i", $land_id);
    $stmt->execute();
    $owner_id = $stmt->get_result()->fetch_assoc()['owner_id'];
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO rentals (land_id, farmer_id, owner_id, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("iii", $land_id, $farmer_id, $owner_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Rental request sent successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send request.']);
    }
    $stmt->close();
}

function getMyRequests($owner_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT r.rental_id, l.title AS land_title, u.name AS farmer_name, r.status FROM rentals r JOIN lands l ON r.land_id = l.land_id JOIN users u ON r.farmer_id = u.user_id WHERE r.owner_id = ? AND r.status = 'pending'");
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];
    while ($row = $result->fetch_assoc()) { $requests[] = $row; }
    echo json_encode(['status' => 'success', 'requests' => $requests]);
    $stmt->close();
}

function getMyApplications($farmer_id) {
    global $conn;
     $stmt = $conn->prepare("SELECT l.title AS land_title, r.status, r.request_date FROM rentals r JOIN lands l ON r.land_id = l.land_id WHERE r.farmer_id = ? ORDER BY r.request_date DESC");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = [];
    while ($row = $result->fetch_assoc()) { $applications[] = $row; }
    echo json_encode(['status' => 'success', 'applications' => $applications]);
    $stmt->close();
}

function handleRequest($user_id) {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $rental_id = $data['rental_id'];
    $new_status = $data['status'];
    
    // An admin can approve any request, while an owner can only approve their own.
    $is_admin = in_array('admin', $_SESSION['user']['roles']);
    
    if ($is_admin) {
        $stmt = $conn->prepare("UPDATE rentals SET status = ? WHERE rental_id = ?");
        $stmt->bind_param("si", $new_status, $rental_id);
    } else {
        // Original logic for owners
        $stmt = $conn->prepare("UPDATE rentals SET status = ? WHERE rental_id = ? AND owner_id = ?");
        $stmt->bind_param("sii", $new_status, $rental_id, $user_id);
    }
    
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        if ($new_status === 'approved') {
            $stmt_get_land = $conn->prepare("SELECT land_id FROM rentals WHERE rental_id = ?");
            $stmt_get_land->bind_param("i", $rental_id);
            $stmt_get_land->execute();
            $land_id = $stmt_get_land->get_result()->fetch_assoc()['land_id'];
            $stmt_get_land->close();
            
            $stmt_update_land = $conn->prepare("UPDATE lands SET status = 'booked' WHERE land_id = ?");
            $stmt_update_land->bind_param("i", $land_id);
            $stmt_update_land->execute();
            $stmt_update_land->close();
        }
        echo json_encode(['status' => 'success', 'message' => "Request has been {$new_status}."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Action failed or you do not have permission.']);
    }
    $stmt->close();
}

function getUnseenNotifications($farmer_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT r.rental_id, l.title AS land_title, r.status FROM rentals r JOIN lands l ON r.land_id = l.land_id WHERE r.farmer_id = ? AND r.notification_seen_by_farmer = FALSE AND (r.status = 'approved' OR r.status = 'rejected')");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) { $notifications[] = $row; }
    echo json_encode(['status' => 'success', 'notifications' => $notifications]);
    $stmt->close();
}

function markNotificationAsSeen($farmer_id) {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $rental_id = $data['rental_id'];
    $stmt = $conn->prepare("UPDATE rentals SET notification_seen_by_farmer = TRUE WHERE rental_id = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $rental_id, $farmer_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    $stmt->close();
}

function getAllPendingRequests($user_id) {
    global $conn;
    // Security check: Only admins can perform this action
    if (!in_array('admin', $_SESSION['user']['roles'])) {
        http_response_code(403);
        die(json_encode(['status' => 'error', 'message' => 'Admin privileges required.']));
    }

    $stmt = $conn->prepare("SELECT r.rental_id, l.title AS land_title, u_farmer.name AS farmer_name, u_owner.name AS owner_name
                            FROM rentals r 
                            JOIN lands l ON r.land_id = l.land_id 
                            JOIN users u_farmer ON r.farmer_id = u_farmer.user_id 
                            JOIN users u_owner ON r.owner_id = u_owner.user_id 
                            WHERE r.status = 'pending'");
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    echo json_encode(['status' => 'success', 'requests' => $requests]);
    $stmt->close();
}

$conn->close();
?>