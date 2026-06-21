<?php

declare(strict_types=1);

/**
 * Barre latérale de navigation (accueil, recherche, profil, admin).
 */
class SidebarView
{
    /**
     * Rend la barre latérale de navigation.
     *
     * @return string HTML de la barre latérale.
     */
    public function show()
    {
        ob_start();

        $session = new SessionManager(new UserModel());
        $userid = $session->getUserId();
        $isAdmin = $session->isAdmin();
        ?>

        <div class="sidebars">
            <div class="sidebar">
                <div class="sidebarFixed">
                    <div class="sidebarFixedContainer">
                        <div class="menuLink">
                            <img src="/<?= Utils::e("assets/solage.png") ?>" alt="Post Image" class="logoImage" />
                        </div>
                        <a href="/" aria-label="Accueil" class="menuLink"> <?php echo file_get_contents('assets/home.svg'); ?>
                            <span class="menuTxt">Accueil</span></a>
                        <a href="/search" aria-label="Rechercher" class="menuLink">
                            <?php echo file_get_contents('assets/wen.svg'); ?>                            <span class="menuTxt">Recherche</span></a>
                        <a href="/user/<?= $userid ?>" aria-label="Profile" class="menuLink">
                            <?php echo file_get_contents('assets/profile.svg'); ?>                            <span class="menuTxt">Profil</span></a>
                        <?php if ($isAdmin) : ?>
                            <a href="/admin" aria-label="Admin" class="menuLink">
                                <?php echo file_get_contents('assets/admin.svg'); ?>
                                <span class="menuTxt">Admin</span></a>
                        <?php endif; ?>

                        <button id="magicButton">
                            <?php echo file_get_contents('assets/plus.svg'); ?>
                            <strong>Nouveau post</strong>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebarMobile">
            <div class="sidebarMobileContainer">
                <a href="/" aria-label="Accueil" class="menuLink"> <?php echo file_get_contents('assets/home.svg'); ?> </a>
                <a href="/search" aria-label="Rechercher" class="menuLink"> <?php echo file_get_contents('assets/wen.svg'); ?> </a>
                <a href="/user/<?= $userid ?>" aria-label="Profile" class="menuLink"> <?php echo file_get_contents('assets/profile.svg'); ?> </a>
                <?php if ($isAdmin) : ?>
                    <a href="/admin" aria-label="Admin" class="menuLink"> <?php echo file_get_contents('assets/admin.svg'); ?> </a>
                <?php endif; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
}
