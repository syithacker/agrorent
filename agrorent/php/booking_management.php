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
    case 'get_my_requests': // For Owners to see incoming requests
        getMyRequests($user_id);
        break;
    case 'get_my_applications': // For Farmers to see their sent applications
        getMyApplications($user_id);
        break;
    case 'handle_request': // For Owners to approve or reject
        handleRequest($user_id);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid booking action.']);
        break;
}

function requestToRent($farmer_id) {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $land_id = $data['land_id'];

    // Get the owner_id from the lands table
    $stmt = $conn->prepare("SELECT owner_id FROM lands WHERE land_id = ?");
    $stmt->bind_param("i", $land_id);
    $stmt->execute();
    $owner_id = $stmt->get_result()->fetch_assoc()['owner_id'];
    $stmt->close();

    // Insert the new rental request with a 'pending' status
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
    // Fetches pending requests for lands owned by the current user
    $stmt = $conn->prepare("SELECT r.rental_id, l.title AS land_title, u.name AS farmer_name, r.status 
                            FROM rentals r 
                            JOIN lands l ON r.land_id = l.land_id 
                            JOIN users u ON r.farmer_id = u.user_id 
                            WHERE r.owner_id = ? AND r.status = 'pending'");
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    echo json_encode(['status' => 'success', 'requests' => $requests]);
    $stmt->close();
}

function getMyApplications($farmer_id) {
    global $conn;
    // Fetches all applications sent by the current user (farmer)
     $stmt = $conn->prepare("SELECT l.title AS land_title, r.status, r.request_date
                            FROM rentals r
                            JOIN lands l ON r.land_id = l.land_id
                            WHERE r.farmer_id = ? ORDER BY r.request_date DESC");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = [];
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
    echo json_encode(['status' => 'success', 'applications' => $applications]);
    $stmt->close();
}

function handleRequest($owner_id) {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $rental_id = $data['rental_id'];
    $new_status = $data['status']; // This will be 'approved' or 'rejected'

    // Update the status of the rental request
    $stmt = $conn->prepare("UPDATE rentals SET status = ? WHERE rental_id = ? AND owner_id = ?");
    $stmt->bind_param("sii", $new_status, $rental_id, $owner_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // If the request was approved, we also update the land's status to 'booked'
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

$conn->close();
?>