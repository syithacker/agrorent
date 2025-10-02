// =====================================================================
// INITIALIZATION ON PAGE LOAD
// =====================================================================
document.addEventListener('DOMContentLoaded', function() {
    const user = JSON.parse(sessionStorage.getItem('user'));

    if (!user) {
        window.location.href = 'login.php';
        return;
    }

    document.getElementById('welcomeMessage').textContent = `Welcome, ${user.name}!`;
    document.getElementById('logoutBtn').addEventListener('click', handleLogout);

    // Show views based on user's roles and fetch initial data
    if (user.roles.includes('farmer')) {
        document.querySelector('.farmer-view').style.display = 'block';
        fetchAllLands();
        fetchMyApplications();
    }
    if (user.roles.includes('owner')) {
        document.querySelector('.owner-view').style.display = 'block';
        document.getElementById('becomeOwnerSection').style.display = 'none';
        fetchMyLands();
        fetchMyRequests();
    }
    if (user.roles.includes('admin')) {
        document.querySelector('.admin-view').style.display = 'block';
        fetchAllPendingRequestsForAdmin();
        fetchPendingLandsForAdmin(); // New function for land approval
    }
    
    // Event listeners
    const addLandForm = document.getElementById('addLandForm');
    if (addLandForm) { addLandForm.addEventListener('submit', handleAddLandSubmit); }
    
    const editLandForm = document.getElementById('editLandForm');
    if(editLandForm) { editLandForm.addEventListener('submit', handleUpdateLandSubmit); }

    const listYourLandBtn = document.getElementById('listYourLandBtn');
    if (listYourLandBtn) { listYourLandBtn.addEventListener('click', handleBecomeOwner); }
});

async function handleLogout(event) {
    event.preventDefault();
    await fetch('php/auth.php?action=logout');
    sessionStorage.removeItem('user');
    window.location.href = 'index.php';
}


// =====================================================================
// CARD & UI CREATION
// =====================================================================
function createLandCard(land) {
    const isBooked = land.status === 'booked';
    const user = JSON.parse(sessionStorage.getItem('user'));
    const isOwner = user && user.id == land.owner_id;

    let statusHTML = '';
    if (isOwner && ['pending_approval', 'rejected'].includes(land.status)) {
        statusHTML = `<span class="card-status status-${land.status}">${land.status.replace('_', ' ')}</span>`;
    }

    let buttonsHTML = '';
    if (isOwner) {
        buttonsHTML = `
            <button class="btn btn-secondary btn-sm" onclick="openEditModal(${land.land_id})">Edit</button>
            <button class="btn btn-danger btn-sm" onclick="deleteLand(${land.land_id})">Delete</button>
        `;
    } else {
        buttonsHTML = `
            <button class="btn btn-primary" ${isBooked ? 'disabled' : ''} onclick="requestToRent(${land.land_id})">
                ${isBooked ? 'Booked' : 'Rent Now'}
            </button>
        `;
    }
    return `
        <div class="land-card ${isBooked ? 'booked' : ''}">
            ${statusHTML}
            <img src="${land.image_url}" alt="${land.title}">
            <div class="card-content">
                <h3>${land.title}</h3>
                <p><strong>Location:</strong> ${land.location}</p>
                <p><strong>Size:</strong> ${land.size}</p>
                <p>${land.description || ''}</p>
            </div>
            <div class="card-footer">
                <span class="price">₹${land.price_per_month}/month</span>
                <div class="card-actions">${buttonsHTML}</div>
            </div>
        </div>
    `;
}


// =====================================================================
// GENERAL & FARMER FUNCTIONS
// =====================================================================
async function fetchAllLands() {
    try {
        const response = await fetch('php/land_management.php?action=list_all_lands');
        const result = await response.json();
        if (result.status === 'success') {
            const container = document.getElementById('land-listings-container');
            container.innerHTML = result.lands.map(createLandCard).join('') || "<p>No lands are currently available for rent.</p>";
        }
    } catch (error) { console.error('Error fetching all lands:', error); }
}

async function requestToRent(landId) {
    if (!confirm("Are you sure you want to send a rental request for this land?")) return;
    try {
        const response = await fetch('php/booking_management.php?action=request_to_rent', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ land_id: landId })
        });
        const result = await response.json();
        alert(result.message);
        if (result.status === 'success') {
            fetchMyApplications();
        }
    } catch (error) { alert('An error occurred while sending your request.'); }
}

async function fetchMyApplications() {
     try {
        const response = await fetch('php/booking_management.php?action=get_my_applications');
        const result = await response.json();
        const container = document.getElementById('my-applications-container');
        if (result.status === 'success' && result.applications.length > 0) {
            let tableHTML = '<table class="data-table"><thead><tr><th>Land Title</th><th>Status</th><th>Date</th></tr></thead><tbody>';
            result.applications.forEach(app => {
                tableHTML += `<tr>
                    <td>${app.land_title}</td>
                    <td><span class="status-badge status-${app.status}">${app.status}</span></td>
                    <td>${new Date(app.request_date).toLocaleDateString()}</td>
                </tr>`;
            });
            container.innerHTML = tableHTML + '</tbody></table>';
        } else {
            container.innerHTML = "<p>You have not applied for any rentals yet.</p>";
        }
    } catch (error) { console.error('Error fetching applications:', error); }
}


// =====================================================================
// LAND OWNER & PRODUCT MANAGEMENT FUNCTIONS
// =====================================================================
async function fetchMyLands() {
    try {
        const response = await fetch('php/land_management.php?action=list_my_lands');
        const result = await response.json();
        if (result.status === 'success') {
            const container = document.getElementById('my-lands-container');
            container.innerHTML = result.lands.map(createLandCard).join('') || "<p>You haven't listed any lands yet.</p>";
        }
    } catch (error) { console.error('Error fetching my lands:', error); }
}

async function handleAddLandSubmit(event) {
    event.preventDefault();
    const messageDiv = document.getElementById('addLandMessage');
    const form = event.target;
    const formData = new FormData(form);

    if (!formData.get('landImage') || !formData.get('landDocument')) {
        messageDiv.textContent = 'Please select both an image and a document.';
        messageDiv.className = 'message message-error';
        messageDiv.style.display = 'block';
        return;
    }
    
    try {
        const response = await fetch('php/land_management.php?action=add_land', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        messageDiv.textContent = result.message;
        if (result.status === 'success') {
            messageDiv.className = 'message message-success';
            form.reset();
            fetchMyLands();
        } else {
            messageDiv.className = 'message message-error';
        }
        messageDiv.style.display = 'block';
    } catch (error) {
        messageDiv.textContent = 'A network or server error occurred. Please try again.';
        messageDiv.className = 'message message-error';
        console.error('Submit Error:', error);
    }
}

async function deleteLand(landId) {
    if (!confirm("Are you sure you want to permanently delete this land listing?")) return;
    try {
        const response = await fetch('php/land_management.php?action=delete_land', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ land_id: landId })
        });
        const result = await response.json();
        alert(result.message);
        if (result.status === 'success') {
            fetchMyLands();
        }
    } catch (error) { alert('An error occurred while deleting the land.'); }
}

async function openEditModal(landId) {
    try {
        const response = await fetch(`php/land_management.php?action=get_land_details&land_id=${landId}`);
        const result = await response.json();
        if (result.status === 'success') {
            const land = result.land;
            document.getElementById('editLandId').value = land.land_id;
            document.getElementById('editLandTitle').value = land.title;
            document.getElementById('editLandLocation').value = land.location;
            document.getElementById('editLandSize').value = land.size;
            document.getElementById('editLandPrice').value = land.price_per_month;
            document.getElementById('editLandDescription').value = land.description;
            document.getElementById('editLandModal').style.display = 'block';
        } else {
            alert(result.message);
        }
    } catch (error) { alert('Failed to fetch land details.'); }
}

async function handleUpdateLandSubmit(event) {
    event.preventDefault();
    const landData = {
        land_id: document.getElementById('editLandId').value,
        title: document.getElementById('editLandTitle').value,
        location: document.getElementById('editLandLocation').value,
        size: document.getElementById('editLandSize').value,
        price: document.getElementById('editLandPrice').value,
        description: document.getElementById('editLandDescription').value
    };
    try {
        const response = await fetch('php/land_management.php?action=update_land', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(landData)
        });
        const result = await response.json();
        alert(result.message);
        if(result.status === 'success') {
            document.getElementById('editLandModal').style.display = 'none';
            fetchMyLands();
        }
    } catch (error) { alert('Failed to update land.'); }
}

// =====================================================================
// BECOME AN OWNER & BOOKING MANAGEMENT
// =====================================================================
async function handleBecomeOwner() {
    if (!confirm("Are you sure you want to unlock landowner features?")) return;

    try {
        const response = await fetch('php/user_management.php?action=add_owner_role', { method: 'POST' });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            document.querySelector('.owner-view').style.display = 'block';
            document.getElementById('becomeOwnerSection').style.display = 'none';
            if (result.user) {
                sessionStorage.setItem('user', JSON.stringify(result.user));
            }
            fetchMyLands();
            fetchMyRequests();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error("Failed to add owner role:", error);
        alert("An error occurred. Please try again.");
    }
}

async function fetchMyRequests() {
    try {
        const response = await fetch('php/booking_management.php?action=get_my_requests');
        const result = await response.json();
        const container = document.getElementById('incoming-requests-container');
        if (result.status === 'success' && result.requests.length > 0) {
            let tableHTML = '<table class="data-table"><thead><tr><th>Land</th><th>Farmer</th><th>Action</th></tr></thead><tbody>';
            result.requests.forEach(req => {
                tableHTML += `<tr>
                    <td>${req.land_title}</td>
                    <td>${req.farmer_name}</td>
                    <td>
                        <p><i>Admin handles approvals.</i></p>
                    </td>
                </tr>`;
            });
            container.innerHTML = tableHTML + '</tbody></table>';
        } else {
            container.innerHTML = "<p>No pending rental requests for your lands.</p>";
        }
    } catch (error) { console.error('Error fetching requests:', error); }
}

async function handleRequest(rentalId, status) {
    try {
        const response = await fetch('php/booking_management.php?action=handle_request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ rental_id: rentalId, status: status })
        });
        const result = await response.json();
        alert(result.message);
        if (result.status === 'success') {
            const user = JSON.parse(sessionStorage.getItem('user'));
            fetchAllLands();
            if (user.roles.includes('owner')) { fetchMyRequests(); fetchMyLands(); }
            if (user.roles.includes('admin')) { fetchAllPendingRequestsForAdmin(); }
        }
    } catch (error) { alert('An error occurred while handling the request.'); }
}


// =====================================================================
// ADMIN FUNCTIONS
// =====================================================================
async function fetchAllPendingRequestsForAdmin() {
    try {
        const response = await fetch('php/booking_management.php?action=get_all_pending_requests');
        const result = await response.json();
        const container = document.getElementById('admin-requests-container');
        if (result.status === 'success' && result.requests.length > 0) {
            let tableHTML = '<table class="data-table"><thead><tr><th>Land Title</th><th>Owner</th><th>Farmer</th><th>Action</th></tr></thead><tbody>';
            result.requests.forEach(req => {
                tableHTML += `<tr>
                    <td>${req.land_title}</td>
                    <td>${req.owner_name}</td>
                    <td>${req.farmer_name}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="handleRequest(${req.rental_id}, 'approved')">Approve</button>
                        <button class="btn btn-secondary btn-sm" onclick="handleRequest(${req.rental_id}, 'rejected')">Reject</button>
                    </td>
                </tr>`;
            });
            container.innerHTML = tableHTML + '</tbody></table>';
        } else {
            container.innerHTML = "<p>No pending rental requests across the platform.</p>";
        }
    } catch (error) { console.error('Error fetching admin requests:', error); }
}

async function fetchPendingLandsForAdmin() {
    try {
        const response = await fetch('php/land_management.php?action=get_pending_lands');
        const result = await response.json();
        const container = document.getElementById('pending-lands-container');
        if (result.status === 'success' && result.lands.length > 0) {
            let tableHTML = '<table class="data-table"><thead><tr><th>Title</th><th>Owner</th><th>Actions</th></tr></thead><tbody>';
            result.lands.forEach(land => {
                tableHTML += `<tr>
                    <td>${land.title}</td>
                    <td>${land.owner_name}</td>
                    <td>
                        <a href="${land.document_url}" target="_blank" class="btn btn-secondary btn-sm">View Doc</a>
                        <button class="btn btn-primary btn-sm" onclick="handleLandApproval(${land.land_id}, 'approve')">Approve</button>
                        <button class="btn btn-danger btn-sm" onclick="handleLandApproval(${land.land_id}, 'reject')">Reject</button>
                    </td>
                </tr>`;
            });
            container.innerHTML = tableHTML + '</tbody></table>';
        } else {
            container.innerHTML = "<p>No new lands are currently awaiting approval.</p>";
        }
    } catch (error) { console.error('Error fetching pending lands:', error); }
}

async function handleLandApproval(landId, status) {
    if (!confirm(`Are you sure you want to ${status} this land listing?`)) return;
    try {
        const response = await fetch('php/land_management.php?action=handle_land_approval', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ land_id: landId, status: status })
        });
        const result = await response.json();
        alert(result.message);
        if(result.status === 'success') {
            fetchPendingLandsForAdmin();
        }
    } catch (error) { alert('An error occurred while updating the land status.'); }
}

// =====================================================================
// NOTIFICATION POLLING SYSTEM
// =====================================================================
async function fetchNotifications() {
    try {
        const response = await fetch('php/booking_management.php?action=get_unseen_notifications');
        const result = await response.json();
        if (result.status === 'success' && result.notifications.length > 0) {
            result.notifications.forEach(displayNotification);
            fetchMyApplications();
        }
    } catch (error) { /* Silently fail */ }
}

function displayNotification(notification) {
    const notificationArea = document.getElementById('notification-area');
    const notificationDiv = document.createElement('div');
    notificationDiv.className = 'notification';
    let title, message, type;
    if (notification.status === 'approved') {
        type = 'is-success';
        title = 'Congratulations! 🎉';
        message = `Your rental request for <strong>${notification.land_title}</strong> has been approved.`;
    } else {
        type = 'is-danger';
        title = 'Update on your application';
        message = `Unfortunately, your rental request for <strong>${notification.land_title}</strong> was not approved.`;
    }
    notificationDiv.classList.add(type);
    notificationDiv.innerHTML = `<button class="delete-notification">&times;</button><p class="notification-title">${title}</p><p>${message}</p>`;
    notificationArea.appendChild(notificationDiv);
    notificationDiv.querySelector('.delete-notification').addEventListener('click', () => {
        notificationDiv.remove();
    });
    markNotificationAsSeen(notification.rental_id);
}

async function markNotificationAsSeen(rentalId) {
    try {
        await fetch('php/booking_management.php?action=mark_notification_as_seen', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ rental_id: rentalId })
        });
    } catch (error) { console.error("Failed to mark notification as seen:", error); }
}

const currentUser = JSON.parse(sessionStorage.getItem('user'));
if (currentUser && currentUser.roles.includes('farmer')) {
    setInterval(fetchNotifications, 5000);
}