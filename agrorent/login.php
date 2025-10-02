<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AgroRent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <a href="index.php" class="navbar-brand" style="display: block; text-align: center; margin-bottom: 20px;">🌱 AgroRent</a>
        <h2>Welcome Back!</h2>
        <form id="loginForm">
            <div class="form-group"><input type="email" id="email" placeholder="Email Address" required></div>
            <div class="form-group"><input type="password" id="password" placeholder="Password" required></div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        <div id="message"></div>
        <p class="auth-switch">Don't have an account? <a href="register.php">Register now</a></p>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');
            
            try {
                const response = await fetch('/agrorent/php/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                const result = await response.json();

                if (result.status === 'success') {
                    sessionStorage.setItem('user', JSON.stringify(result.user));
                    messageDiv.textContent = 'Login successful! Redirecting...';
                    messageDiv.className = 'message-success';
                    messageDiv.style.display = 'block';
                    setTimeout(() => {
                        window.location.href = 'dashboard.php'; // Updated redirect
                    }, 1500);
                } else {
                    messageDiv.textContent = result.message;
                    messageDiv.className = 'message-error';
                    messageDiv.style.display = 'block';
                }
            } catch (error) {
                messageDiv.textContent = 'A network error occurred. Please try again.';
                messageDiv.className = 'message-error';
                messageDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>