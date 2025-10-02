<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AgroRent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <a href="index.php" class="navbar-brand" style="display: block; text-align: center; margin-bottom: 20px;">🌱 AgroRent</a>
        <h2>Create Your Account</h2>
        <form id="registerForm">
            <div class="form-group"><input type="text" id="name" placeholder="Full Name" required></div>
            <div class="form-group"><input type="email" id="email" placeholder="Email Address" required></div>
            <div class="form-group"><input type="password" id="password" placeholder="Password" required></div>
            <div class="form-group"><input type="tel" id="phone" placeholder="Phone Number"></div>
            <div class="form-group"><input type="text" id="address" placeholder="Address"></div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
        </form>
        <div id="message"></div>
        <p class="auth-switch">Already have an account? <a href="login.php">Login</a></p>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value
            };
            const messageDiv = document.getElementById('message');
            try {
                const response = await fetch('/agrorent/php/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                const result = await response.json();
                messageDiv.textContent = result.message;
                if (result.status === 'success') {
                    messageDiv.className = 'message-success';
                    setTimeout(() => {
                        window.location.href = 'login.php'; // Updated redirect
                    }, 2000);
                } else {
                    messageDiv.className = 'message-error';
                }
                messageDiv.style.display = 'block';
            } catch (error) {
                messageDiv.textContent = 'A network error occurred. Please try again.';
                messageDiv.className = 'message-error';
                messageDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>