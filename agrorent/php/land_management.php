<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Publicly accessible action doesn't need a session check
if ($action === 'list_all_lands') {
    listAllLands();
    exit;
}

// All other actions require a logged-in user
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Access denied. Please log in.']));
}

$user_id = $_SESSION['user']['id'];

switch ($action) {
    case 'list_my_lands':
        listMyLands($user_id);
        break;
    case 'add_land':
        addLand($user_id);
        break;
    case 'get_land_details':
        getLandDetails($user_id);
        break;
    case 'update_land':
        updateLand($user_id);
        break;
    case 'delete_land':
        deleteLand($user_id);
        break;
    case 'get_pending_lands':
        getPendingLands($user_id);
        break;
    case 'handle_land_approval':
        handleLandApproval($user_id);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid land action.']);
        break;
}

function listAllLands() {
    global $conn;
    // Only show lands that have been approved by an admin
    $result = $conn->query("SELECT * FROM lands WHERE status = 'available'");
    $lands = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $lands[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'lands' => $lands]);
}

function listMyLands($owner_id) {
    global $conn;
    if (!in_array('owner', $_SESSION['user']['roles'])) {
        http_response_code(403);
        die(json_encode(['status' => 'error', 'message' => 'You are not an owner.']));
    }
    
    $stmt = $conn->prepare("SELECT * FROM lands WHERE owner_id = ? ORDER BY land_id DESC");
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

// =============================================================
// --- THIS IS THE FUNCTION THAT ADDS A NEW LAND ---
// =============================================================
function addLand($owner_id) {
    global $conn;
    if (!in_array('owner', $_SESSION['user']['roles'])) {
        http_response_code(403);
        die(json_encode(['status' => 'error', 'message' => 'Only owners can add land.']));
    }

    // Helper function for uploading files (images and documents)
    function uploadFile($file_key, $prefix) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            // Create the directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_tmp_path = $_FILES[$file_key]['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
            $new_file_name = uniqid($prefix, true) . '.' . $file_ext;
            $dest_path = $upload_dir . $new_file_name;
            $allowed_mime_types = ['image/jpeg', 'image/png', 'application/pdf'];
            if (in_array($_FILES[$file_key]['type'], $allowed_mime_types) && $_FILES[$file_key]['size'] < 5000000) { // 5MB limit
                if (move_uploaded_file($file_tmp_path, $dest_path)) {
                    return 'uploads/' . $new_file_name; // Return the relative path for the database
                }
            }
        }
        return null;
    }

    $image_url = uploadFile('landImage', 'img_');
    $document_url = uploadFile('landDocument', 'doc_');

    if (!$image_url || !$document_url) {
        die(json_encode(['status' => 'error', 'message' => 'File upload failed. Please check file types (JPG, PNG, PDF) and size (max 5MB).']));
    }

    // Set the land status to 'pending_approval'
    $status = 'pending_approval';

    $stmt = $conn->prepare("INSERT INTO lands (owner_id, title, location, size, price_per_month, description, image_url, document_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdssss", $owner_id, $_POST['title'], $_POST['location'], $_POST['size'], $_POST['price'], $_POST['description'], $image_url, $document_url, $status);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Land submitted successfully! It is now awaiting admin approval.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database insertion failed.']);
    }
    $stmt->close();
}
// =============================================================
// --- END OF addLand FUNCTION ---
// =============================================================

function getLandDetails($owner_id) {
    global $conn;
    $land_id = $_GET['land_id'] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM lands WHERE land_id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $land_id, $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($land = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'land' => $land]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Land not found or you do not have permission.']);
    }
    $stmt->close();
}

function updateLand($owner_id) {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $conn->prepare("UPDATE lands SET title = ?, location = ?, size = ?, price_per_month = ?, description = ? WHERE land_id = ? AND owner_id = ?");
    $stmt->bind_param("sssdsii", $data['title'], $data['location'], $data['size'], $data['price'], $data['description'], $data['land_id'], $owner_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Land updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No changes were made or you do not have permission.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update land.']);
    }
    $stmt->close();
}

function deleteLand($owner_id) {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $land_id = $data['land_id'];

    $stmt = $conn->prepare("DELETE FROM lands WHERE land_id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $land_id, $owner_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Land deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Land not found or you do not have permission.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete land.']);
    }
    $stmt->close();
}

function getPendingLands($user_id) {
    global $conn;
    if (!in_array('admin', $_SESSION['user']['roles'])) {
        http_response_code(403);
        die(json_encode(['status' => 'error', 'message' => 'Admin privileges required.']));
    }
    $result = $conn->query("SELECT l.*, u.name as owner_name FROM lands l JOIN users u ON l.owner_id = u.user_id WHERE l.status = 'pending_approval'");
    $lands = [];
    while ($row = $result->fetch_assoc()) {
        $lands[] = $row;
    }
    echo json_encode(['status' => 'success', 'lands' => $lands]);
}

function handleLandApproval($user_id) {
    global $conn;
    if (!in_array('admin', $_SESSION['user']['roles'])) {
        http_response_code(403);
        die(json_encode(['status' => 'error', 'message' => 'Admin privileges required.']));
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $land_id = $data['land_id'];
    $new_status = $data['status'] === 'approve' ? 'available' : 'rejected';

    $stmt = $conn->prepare("UPDATE lands SET status = ? WHERE land_id = ?");
    $stmt->bind_param("si", $new_status, $land_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Land status updated.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update land status.']);
    }
    $stmt->close();
}

$conn->close();
?>