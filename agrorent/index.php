<?php
    require_once 'php/db.php';

    $lands = [];
    $result = $conn->query("SELECT * FROM lands WHERE status = 'available'");
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $lands[] = $row;
        }
    }
    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroRent - Rent Farmland with Ease</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">🌱 AgroRent</a>
        <div class="nav-links">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="dashboard.php" class="btn btn-secondary">My Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
        <div class="dashboard-section">
            <h1 class="dashboard-title">Available Farmland for Rent</h1>
            <p style="text-align: center; max-width: 600px; margin: -2rem auto 3rem;">Browse our listings. Click "Rent Now" to log in and start the process.</p>
            <div class="card-container">
                <?php if (count($lands) > 0): ?>
                    <?php foreach ($lands as $land): ?>
                        <div class="land-card">
                            <img src="<?php echo htmlspecialchars($land['image_url']); ?>" alt="<?php echo htmlspecialchars($land['title']); ?>">
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($land['title']); ?></h3>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($land['location']); ?></p>
                                <p><strong>Size:</strong> <?php echo htmlspecialchars($land['size']); ?></p>
                            </div>
                            <div class="card-footer">
                                <span class="price">₹<?php echo htmlspecialchars($land['price_per_month']); ?>/month</span>
                                <a href="login.php" class="btn btn-primary">Rent Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No lands are currently available for rent. Please check back later.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>