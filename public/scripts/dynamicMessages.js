document.addEventListener('DOMContentLoaded', function() {
  // Vérification si loginForm existe avant d'ajouter l'événement
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();

      let email = document.getElementById('email').value;
      let password = document.getElementById('password').value;

      // Effectuer une requête AJAX pour valider le mot de passe
      fetch('/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
      })
        .then(response => response.json())
        .then(data => {
          console.log(data)

          if (data.success === 'success') {
            window.location.href = '/';
          }
          const messageContainer = document.getElementById('messageContainer');
          messageContainer.innerHTML = data.divMessageHtml;
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

      // Effectuer une requête AJAX pour l'inscription
      fetch('/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&name=${encodeURIComponent(username)}`
      })
        .then(response => response.json())
        .then(data => {
          console.log(data);

          if (data.success === 'success') {
            // Rediriger vers une autre page après inscription réussie
            window.location.href = '/';
          }

          // Afficher le message d'erreur ou de succès dans le conteneur
          const messageContainer = document.getElementById('messageContainer');
          messageContainer.innerHTML = data.divMessageHtml;
        })
        .catch(error => {
          console.error('Error:', error);
        });
    });
  }
});
