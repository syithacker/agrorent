document.getElementById('registerForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const phone = document.getElementById('phone').value;
    const address = document.getElementById('address').value;
    const messageDiv = document.getElementById('message');
    const selectedRoles = Array.from(document.querySelectorAll('input[name="roles"]:checked')).map(cb => cb.value);

    if (selectedRoles.length === 0) {
        messageDiv.textContent = 'Please select at least one role.';
        messageDiv.className = 'message-error';
        messageDiv.style.display = 'block';
        return;
    }
    
    const formData = { name, email, password, phone, address, roles: selectedRoles };

    try {
        const response = await fetch('php/auth.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        const result = await response.json();
        messageDiv.textContent = result.message;
        if (result.status === 'success') {
            messageDiv.className = 'message-success';
            setTimeout(() => { window.location.href = 'login.html'; }, 2000);
        } else {
            messageDiv.className = 'message-error';
        }
        messageDiv.style.display = 'block';
    } catch (error) {
        messageDiv.textContent = 'An error occurred. Please try again.';
        messageDiv.className = 'message-error';
        messageDiv.style.display = 'block';
    }
});