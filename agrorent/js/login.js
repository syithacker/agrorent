document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const messageDiv = document.getElementById('message');
    
    try {
        const response = await fetch('php/auth.php?action=login', {
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
            setTimeout(() => { window.location.href = 'dashboard.html'; }, 1500);
        } else {
            messageDiv.textContent = result.message;
            messageDiv.className = 'message-error';
            messageDiv.style.display = 'block';
        }
    } catch (error) {
        messageDiv.textContent = 'An error occurred. Please try again.';
        messageDiv.className = 'message-error';
        messageDiv.style.display = 'block';
    }
});