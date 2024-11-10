<?php

class EditUserView {
    protected $user;

    public function __construct($user) {
        $this->user = $user;
    }

    public function show() {
        ob_start();
        ?>

        <div class="edit-user-profile">
            <h1>Modifier le profil de <?= htmlspecialchars($this->user->getName() ?? '') ?></h1>

            <form action="/edituser/<?= $this->user->getId() ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nom:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($this->user->getName() ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($this->user->getEmail() ?? '') ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" name="password" value="">
                </div>

                <!-- Conteneur pour les boutons -->
                <div class="button-container">
                    <input type="submit" value="Mettre à jour le profil">
                    <input type="button" value="Annuler" onclick="window.location.href='/user/<?= $this->user->getId() ?>'">
                </div>
            </form>
        </div>

        <?php
        (new LayoutView('Modifier le profil', 'Éditez vos informations personnelles', ob_get_clean()))->show();
    }
}
?>
