document.getElementById('authForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    fetch(`http://localhost/BetsMinistry/api/?Command=AuthUserCommand&username=${username}&password=${password}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                localStorage.setItem('userId', data.response.userId); // Save user ID
                localStorage.setItem('roleId', data.response.roleId); // Save user role ID
                window.location.href = "../events/events.html"; // Redirect after successful login
            } else {
                document.getElementById('message').innerText = data.message; // Display error message
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('message').innerText = 'An error occurred. Please try again.';
        });
});
