/////////// Create a post

let selectedImage = null;
const postContentImageContainer = document.getElementById('postContentImageContainer');
const removeImageButton = document.getElementById('removeImageButton');

// Écouter l'événement de changement sur l'input de fichier
const imageInput = document.getElementById("file-input");

if (imageInput) {

  document.getElementById("file-input").addEventListener("change", (event) => {
    const file = imageInput.files[0];

    if (file) {
      const reader = new FileReader();
      reader.onload = (event) => {
        const img = document.createElement('img');
        img.src = event.target.result;
        img.style.maxWidth = '100%';
        img.style.display = 'block';
        img.classList.add('postContentImage')
        postContentImageContainer.innerHTML = '';
        postContentImageContainer.appendChild(img);

        // Afficher le bouton de suppression
        removeImageButton.style.display = 'block';
      };
      reader.readAsDataURL(file);
      selectedImage = file;
    }
  });

  // Logique pour le bouton de suppression de l'image
  removeImageButton.addEventListener('click', () => {
    // Réinitialiser l'aperçu de l'image et l'input fichier
    postContentImageContainer.innerHTML = '';
    imageInput.value = ''; // Réinitialiser l'input de fichier
    selectedImage = null; // Réinitialiser l'image sélectionnée

    // Cacher le bouton de suppression
    removeImageButton.style.display = 'none';
  });

  document.getElementById("file-input").addEventListener('submit', () => {
    event.preventDefault();
  })
}

if (document.getElementById("postCreateButton")) {
  document.getElementById("postCreateButton").addEventListener("click", (event) => {
    const content = document.getElementById("postContent").innerText.trim();

    if (!content) {
      alert("Le contenu du post ne peut pas être vide !");
      return;
    }

    const postData = {
      content: content,
    };

    // Si c'est une reply, alors ajouter reply:postId et replyToParent:postId
    const postToReplyTo = event.target.getAttribute('data-posttoreply')
    const postParent = event.target.getAttribute('data-postParent')

    if( postToReplyTo !== '') {
      postData.replyTo = postToReplyTo;
    } else {
      postData.replyTo = null;
    }

    if( postParent !== null) {
      postData.replyToParent = postParent;
    } else {
      postData.replyToParent = null;
    }

    // FormData pour l'image

    const formData = new FormData();
    formData.append('data', JSON.stringify(postData)); // Ajoute le JSON
    if (selectedImage) {
      formData.append('image', selectedImage); // Ajoute l'image
    }

    fetch("/api/post", {
      method: "POST",
      body: formData,
    })
        .then(response => {
          if (!response.ok) {
            throw new Error("Erreur réseau");
          }
          return response.json();
        })
        .then(data => {
          console.log(data)

          if (data.success) {
            appendNewPostToList(data);
            postOnClickPage();
            document.getElementById("postContent").innerText = '';
            postContentImageContainer.innerText = "";
            document.getElementById("file-input").value = ''; // Réinitialise l'input de fichier
            document.getElementById("postContent").scrollIntoView({ behavior: 'smooth', block: 'center' });

          } else {
            alert("Erreur lors de la création du post : " + data.message);
          }
        })
        .catch(error => {
          console.error("Erreur:", error);
          alert("Une erreur est survenue. Veuillez réessayer.");
        });
  });

}

function appendNewPostToList(data) {
  const post = data.data;

  const postList = document.getElementById("postList");
  const newPost = document.createElement("div");
  newPost.classList.add("post");
  newPost.setAttribute('data-id', post.id);
  newPost.innerHTML = `<div class="postAvatarContainer"><div class="postAvatar">${post.userimage}</div></div><div class="postInsideContainer"><div class="postNameDate"><div>${post.username}</div><div class="postDate"><?= $post->${post.date}</div></div><div class="postContentTools"><div class="postContent">${post.content}</div>${post.image ? `<img src="/uploaded_files/${post.image}" alt="" class="postImage" />` : ''}<div class="postTools"><div class="postTool"><div class="icon"><svg width="22" class="response" height="22" viewBox="0 0 24 24" fill="var(--secondary)" xmlns="http://www.w3.org/2000/svg"><path d="M7 9H17M7 13H12M21 20L17.6757 18.3378C17.4237 18.2118 17.2977 18.1488 17.1656 18.1044C17.0484 18.065 16.9277 18.0365 16.8052 18.0193C16.6672 18 16.5263 18 16.2446 18H6.2C5.07989 18 4.51984 18 4.09202 17.782C3.71569 17.5903 3.40973 17.2843 3.21799 16.908C3 16.4802 3 15.9201 3 14.8V7.2C3 6.07989 3 5.51984 3.21799 5.09202C3.40973 4.71569 3.71569 4.40973 4.09202 4.21799C4.51984 4 5.0799 4 6.2 4H17.8C18.9201 4 19.4802 4 19.908 4.21799C20.2843 4.40973 20.5903 4.71569 20.782 5.09202C21 5.51984 21 6.0799 21 7.2V20Z" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span class="menuTxt">0</span></div><div class="postTool"><div class="icon"><svg width="22" class="heart" height="22" vipostewBox="0 0 24 24" fill="var(--secondary)" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span class="countLikes">0</span></div></div></div></div>`
  postList.prepend(newPost);

}

//

document.addEventListener('click', function(event){
  let svgElement = event.target.closest(".postTool svg");

  let postId;
  if (svgElement) {
    // Si l'élement cliqué n'est pas le like, stop
    if (!svgElement.classList.contains('heart')) {
      return;
    }
    let postElement = svgElement.closest(".post");

    if (postElement) {
      postId = postElement.getAttribute("data-id");

    }

    if (!postId) {
      alert("Le post visé n'a pas d'ID");
      return;
    }

    const postData = {
      post: postId,
    };

    fetch("/api/like", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(postData)
    })
        .then(response => {
          if (!response.ok) {
            throw new Error("Erreur réseau");
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            const countLikes = svgElement.closest('.postTool').querySelector('.countLikes');
            let currentLikes = parseInt(countLikes.textContent.trim());

            if (!svgElement.classList.contains('active')) {
              svgElement.classList.add('active')
              countLikes.textContent = currentLikes + 1;
            } else {
              svgElement.classList.remove('active')
              countLikes.textContent = currentLikes - 1;
            }
          } else {
            alert("Erreur lors de la création du post : " + data.message);
          }
        })
        .catch(error => {
          console.error("Erreur:", error);
          alert("Une erreur est survenue. Veuillez réessayer.");
        });
  }
})

// When click on a post, go to post page

document.addEventListener("DOMContentLoaded", (event) => {
  postOnClickPage()
});

function postOnClickPage() {
  let posts = document.querySelectorAll('.post')

  if (posts) {
    posts.forEach((post) => {
      const postId = post.getAttribute('data-id');

      post.addEventListener('click', (event) => {
        if (event.target.closest('svg') ) {
          // Si l'élément cliqué est un SVG, on arrête le traitement
          return;
        }
        window.location.href = `/post/${postId}`;
      })
    })
  }
}


// When click on Nouveau Post, go to createPostButton

document.addEventListener("DOMContentLoaded", () => {
  const button = document.getElementById("magicButton");
  const editableElement = document.getElementById("postContent");
  console.log(editableElement)

  button.addEventListener("click", () => {
    const range = document.createRange();
    const selection = window.getSelection();
    range.selectNodeContents(editableElement);
    range.collapse(false);

    selection.removeAllRanges();
    selection.addRange(range);

    editableElement.focus();
    editableElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

  });
});

// Boutton pour remonter

// Fonction pour détecter le scroll
window.onscroll = function() {
  scrollFunction();
};

function scrollFunction() {
  const scrollTopBtn = document.getElementById("scrollTopBtn");
  // Si on dépasse 100vh, le bouton apparaît
  if (document.documentElement.scrollTop > window.innerHeight) {
    scrollTopBtn.style.display = "block";
  } else {
    scrollTopBtn.style.display = "none";
  }
}

// Quand l'utilisateur clique, remonter en haut
document.getElementById("scrollTopBtn").addEventListener("click", function() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});


