function getCursorPosition(element, event) {
  const rect = element.getBoundingClientRect();
  const centerX = rect.left + rect.width / 2;
  const centerY = rect.top + rect.height / 2;
  const x = event.clientX - centerX;
  const y = centerY - event.clientY;
  return { x, y };
}

const buttons = document.querySelectorAll("button");
[...buttons].map((button) => {
  button.addEventListener("pointermove", (event) => {
    const { x, y } = getCursorPosition(event.target, event);
    button.style.setProperty("--coord-x", x);
    button.style.setProperty("--coord-y", y);
  });
  button.addEventListener("pointerleave", (event) => {
    button.style.setProperty("--coord-x", 0);
    button.style.setProperty("--coord-y", 0);
  });
});

// Create a post
document.getElementById("postCreateButton").addEventListener("click", () => {
  const content = document.getElementById("postContent").innerText.trim();
  const user = 1;

  if (!content) {
    alert("Le contenu du post ne peut pas être vide !");
    return;
  }

  const postData = {
    user: user,
    content: content,
  };

  fetch("/api/post", {
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
        console.log(data);
        appendNewPostToList(data);
        if (data.success) {
          document.getElementById("postContent").innerText = '';
        } else {
          alert("Erreur lors de la création du post : " + data.message);
        }
      })
      .catch(error => {
        console.error("Erreur:", error);
        alert("Une erreur est survenue. Veuillez réessayer.");
      });
});

function appendNewPostToList(data) {
  const { post } = data;
  const postList = document.getElementById("postList");
  const newPost = document.createElement("div");

  newPost.classList.add("post");
  newPost.innerHTML = `
      <div class="postAvatarContainer"><img class="postAvatar" src="https://pbs.twimg.com/profile_images/1834449929932062720/3j3_C2V5_400x400.jpg" alt=""></div>
      <div class="postInsideContainer">
          <div class="postNameDate">
              <div>${post.user}</div>
              <div class="postDate"><?= $post->${post.date}</div>
          </div>
          <div class="postContentTools">
              <div class="postContent">${post.content}</div>
              <div class="postTools">
                  <div class="postTool">
                      <div class="icon">
                          <?xml version="1.0" encoding="utf-8"?><!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                          <svg width="22" class="" height="22" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                          <path d="M7 9H17M7 13H12M21 20L17.6757 18.3378C17.4237 18.2118 17.2977 18.1488 17.1656 18.1044C17.0484 18.065 16.9277 18.0365 16.8052 18.0193C16.6672 18 16.5263 18 16.2446 18H6.2C5.07989 18 4.51984 18 4.09202 17.782C3.71569 17.5903 3.40973 17.2843 3.21799 16.908C3 16.4802 3 15.9201 3 14.8V7.2C3 6.07989 3 5.51984 3.21799 5.09202C3.40973 4.71569 3.71569 4.40973 4.09202 4.21799C4.51984 4 5.0799 4 6.2 4H17.8C18.9201 4 19.4802 4 19.908 4.21799C20.2843 4.40973 20.5903 4.71569 20.782 5.09202C21 5.51984 21 6.0799 21 7.2V20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                      </div>
                      <span class="menuTxt">Recherche</span></a>
                      0
                      </div>
                  <div class="postTool">
                    <div class="icon">
                      <?xml version="1.0" encoding="utf-8"?><!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                      <svg width="22" class="" height="22" vipostewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </div>
                      0
                    </div>
              </div>
          </div>
      </div>
  `;

  postList.prepend(newPost);  // Ajouter au début de la liste des posts

}

document.getElementById("file-input").addEventListener('submit', () => {
  event.preventDefault();
})

//

document.addEventListener('click', function(event){
  let svgElement = event.target.closest(".postTool svg");
  let postId;
  if (svgElement) {
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
            alert('like ok !')
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


