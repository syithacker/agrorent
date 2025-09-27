document.addEventListener('DOMContentLoaded', function() {
    const user = JSON.parse(sessionStorage.getItem('user'));

    if (!user) {
        window.location.href = 'login.html';
        return;
    }

    document.getElementById('welcomeMessage').textContent = `Welcome, ${user.name}!`;

    document.getElementById('logoutBtn').addEventListener('click', async (e) => {
        e.preventDefault();
        await fetch('php/auth.php?action=logout');
        sessionStorage.removeItem('user');
        window.location.href = 'login.html';
    });

    // Show views based on roles and fetch initial data
    if (user.roles.includes('farmer')) {
        document.querySelector('.farmer-view').style.display = 'block';
        fetchAllLands();
        fetchMyApplications(); 
    }
    if (user.roles.includes('owner')) {
        document.querySelector('.owner-view').style.display = 'block';
        fetchMyLands();
        fetchMyRequests(); 
    }
    if (user.roles.includes('admin')) {
        document.querySelector('.admin-view').style.display = 'block';
    }
    
    // Event listener for the "Add Land" form
    const addLandForm = document.getElementById('addLandForm');
    if (addLandForm) {
        addLandForm.addEventListener('submit', handleAddLandSubmit);
    }
});

// --- CARD CREATION ---
function createLandCard(land) {
    const isBooked = land.status === 'booked';
    return `
        <div class="land-card ${isBooked ? 'booked' : ''}">
            <img src="${land.image_url}" alt="${land.title}">
            <div class="card-content">
                <h3>${land.title}</h3>
                <p><strong>Location:</strong> ${land.location}</p>
                <p><strong>Size:</strong> ${land.size}</p>
                <p>${land.description || ''}</p>
            </div>
            <div class="card-footer">
                <span class="price">₹${land.price_per_month}/month</span>
                <button class="btn btn-primary" ${isBooked ? 'disabled' : ''} onclick="requestToRent(${land.land_id})">
                    ${isBooked ? 'Booked' : 'Rent Now'}
                </button>
            </div>
        </div>
    `;
}

// --- DATA FETCHING FUNCTIONS ---
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

// --- LAND OWNER FUNCTIONS ---
async function handleAddLandSubmit(event) {
    event.preventDefault();
    const landData = {
        title: document.getElementById('landTitle').value,
        location: document.getElementById('landLocation').value,
        size: document.getElementById('landSize').value,
        price: document.getElementById('landPrice').value,
        description: document.getElementById('landDescription').value
    };
    const messageDiv = document.getElementById('addLandMessage');
    try {
        const response = await fetch('php/land_management.php?action=add_land', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(landData)
        });
        const result = await response.json();
        messageDiv.textContent = result.message;
        if (result.status === 'success') {
            messageDiv.className = 'message message-success';
            this.reset();
            fetchMyLands();
        } else {
            messageDiv.className = 'message message-error';
        }
        messageDiv.style.display = 'block';
    } catch (error) {
        messageDiv.textContent = 'An error occurred. Please try again.';
        messageDiv.className = 'message message-error';
        messageDiv.style.display = 'block';
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
                        <button class="btn btn-primary btn-sm" onclick="handleRequest(${req.rental_id}, 'approved')">Approve</button>
                        <button class="btn btn-secondary btn-sm" onclick="handleRequest(${req.rental_id}, 'rejected')">Reject</button>
                    </td>
                </tr>`;
            });
            container.innerHTML = tableHTML + '</tbody></table>';
        } else {
            container.innerHTML = "<p>No pending rental requests.</p>";
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
            fetchMyRequests(); // Refresh the request list
            fetchMyLands();    // Refresh my land list to show "booked" status
            fetchAllLands();   // Refresh all lands list for farmers
        }
    } catch (error) {
        alert('An error occurred while handling the request.');
    }
}

// --- FARMER FUNCTIONS ---
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
            fetchMyApplications(); // Refresh the farmer's application list
        }
    } catch (error) {
        alert('An error occurred while sending your request.');
    }
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