<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AgroRent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">🌱 AgroRent</a>
        <div class="nav-links">
            <span id="welcomeMessage"></span>
            <a href="#" id="logoutBtn" class="btn btn-secondary">Logout</a>
        </div>
    </nav>

    <main class="container">
        <div id="notification-area"></div>

        <h1 class="dashboard-title">Your Dashboard</h1>

        <div class="role-view farmer-view" style="display: none;">
            <section id="becomeOwnerSection" class="dashboard-section become-owner-prompt">
                <h2>Want to lease your own land?</h2>
                <p>Click the button below to unlock landowner features and start listing your properties for rent.</p>
                <button id="listYourLandBtn" class="btn btn-primary">Become a Landowner</button>
            </section>

            <section class="dashboard-section">
                <h2>My Rental Applications</h2>
                <div id="my-applications-container">
                    </div>
            </section>
            
            <section class="dashboard-section">
                <h2>Available Lands for Rent</h2>
                <div id="land-listings-container" class="card-container">
                    </div>
            </section>
        </div>

        <div class="role-view owner-view" style="display: none;">
            <section class="dashboard-section">
                <h2>Incoming Rental Requests</h2>
                <div id="incoming-requests-container">
                    </div>
            </section>

            <section class="dashboard-section">
                <h2>Add a New Land Listing</h2>
                <form id="addLandForm" class="add-land-form">
                    <div class="form-row">
                        <input type="text" name="title" id="landTitle" placeholder="Land Title (e.g., Sunny Meadow Fields)" required>
                        <input type="text" name="location" id="landLocation" placeholder="Location (e.g., Willow Creek, Punjab)" required>
                    </div>
                    <div class="form-row">
                        <input type="text" name="size" id="landSize" placeholder="Size (e.g., 10 Acres)" required>
                        <input type="number" name="price" id="landPrice" placeholder="Price per Month (₹)" required>
                    </div>
                    <div class="form-group">
                        <label for="landImage">Land Image (JPG, PNG)</label>
                        <input type="file" name="landImage" id="landImage" accept="image/png, image/jpeg" required>
                    </div>
                    <div class="form-group">
                        <label for="landDocument">Ownership Document (PDF)</label>
                        <input type="file" name="landDocument" id="landDocument" accept="application/pdf" required>
                    </div>
                    <textarea name="description" id="landDescription" placeholder="Describe the land, its features, and suitable crops..." required></textarea>
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                    <div id="addLandMessage" class="message" style="display: none; margin-top: 15px;"></div>
                </form>
            </section>

            <section class="dashboard-section">
                <h2>My Land Listings</h2>
                <div id="my-lands-container" class="card-container">
                     </div>
            </section>
        </div>
        
        <div class="role-view admin-view" style="display: none;">
            <section class="dashboard-section">
                <h2>Admin Overview</h2>
                <div class="stat-cards-container">
                    <div class="stat-card"><div class="stat-info"><span class="stat-title">Total Users</span><span class="stat-value">125</span></div></div>
                    <div class="stat-card"><div class="stat-info"><span class="stat-title">Lands Listed</span><span class="stat-value">48</span></div></div>
                    <div class="stat-card"><div class="stat-info"><span class="stat-title">Pending Requests</span><span class="stat-value">6</span></div></div>
                    <div class="stat-card"><div class="stat-info"><span class="stat-title">Active Rentals</span><span class="stat-value">22</span></div></div>
                </div>
            </section>

            <section class="dashboard-section">
                <h2>New Lands Awaiting Approval</h2>
                <div id="pending-lands-container">
                    </div>
            </section>
            
            <section class="dashboard-section">
                <h2>Pending Rental Approvals (All)</h2>
                 <div id="admin-requests-container">
                    </div>
            </section>

            <section class="dashboard-section">
                <h2>Recent User Registrations</h2>
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Role(s)</th><th>Action</th></tr></thead>
                    <tbody>
                        <tr><td>Atharva Dhuri</td><td>admin@agr.com</td><td><span class="role-tag owner">Owner</span><span class="role-tag farmer">Farmer</span></td><td><button class="btn btn-secondary btn-sm">View</button></td></tr>
                        <tr><td>Jane Smith</td><td>jane@example.com</td><td><span class="role-tag farmer">Farmer</span></td><td><button class="btn btn-secondary btn-sm">View</button></td></tr>
                    </tbody>
                </table>
            </section>
        </div>

        <div id="editLandModal" class="modal">
            <div class="modal-content">
                <span class="close-button" onclick="document.getElementById('editLandModal').style.display='none'">&times;</span>
                <h2>Edit Land Listing</h2>
                <form id="editLandForm" class="add-land-form">
                    <input type="hidden" id="editLandId">
                    <div class="form-group"><label for="editLandTitle">Title</label><input type="text" id="editLandTitle" required></div>
                    <div class="form-group"><label for="editLandLocation">Location</label><input type="text" id="editLandLocation" required></div>
                    <div class="form-group"><label for="editLandSize">Size</label><input type="text" id="editLandSize" required></div>
                    <div class="form-group"><label for="editLandPrice">Price</label><input type="number" id="editLandPrice" required></div>
                    <div class="form-group"><label for="editLandDescription">Description</label><textarea id="editLandDescription" rows="4"></textarea></div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
</body>
</html>