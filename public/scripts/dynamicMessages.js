document.addEventListener('DOMContentLoaded', function() {
  // Vérification si loginForm existe avant d'ajouter l'événement
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();

      let email = document.getElementById('email').value;
      let password = document.getElementById('password').value;

      fetch('/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
      })
        .then(response => response.json() )
        .then(data => {
          if (data.success) {
            window.location.href = '/';
          }
          const type = data.success ? 'success' : 'error';
          const messageContainer = document.getElementById('messageContainer');
          messageContainer.innerHTML =
            `<div class="${type} dynamicMessage"><p>${escapeHtml(data.message)}</p></div>`;
        })
        .catch(error => {
          console.error('Error:', error);
        });
    });
  }

  // Vérification si registerForm existe avant d'ajouter l'événement
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
      e.preventDefault();

      let email = document.getElementById('email').value;
      let password = document.getElementById('password').value;
      let username = document.getElementById('name').value;  // Si vous avez un champ pour le nom d'utilisateur

      fetch('/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&name=${encodeURIComponent(username)}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = '/';
          }
          const type = data.success ? 'success' : 'error';
          const messageContainer = document.getElementById('messageContainer');
          messageContainer.innerHTML =
            `<div class="${type} dynamicMessage"><p>${escapeHtml(data.message)}</p></div>`;
        })
        .catch(error => {
          console.error('Error:', error);
        });
    });
  }
});
