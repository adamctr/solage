<?php

class SidebarView {

    /**
     * @return string
     */
    public function show() {
        ob_start();
        ?>

        <div class="sidebars">
            <div class="sidebar">
                <div class="sidebarFixed">
                    <div class="sidebarFixedContainer">
                        <div class="menuLink">
                            <?php echo file_get_contents('assets/y.svg' ); ?>
                        </div>
                        <a href="/" aria-label="Accueil" class="menuLink"> <?php echo file_get_contents('assets/home.svg' ); ?>
                            <span class="menuTxt">Accueil</span></a>
                        <a href="/search" aria-label="Rechercher" class="menuLink">
                            <?php echo file_get_contents('assets/wen.svg' ); ?>                            <span class="menuTxt">Recherche</span></a>
                        <a href="/profile" aria-label="Profile" class="menuLink">
                            <?php echo file_get_contents('assets/profile.svg' ); ?>                            <span class="menuTxt">Profil</span></a>
                        <a href="/admin" aria-label="Admin" class="menuLink">
                            <?php echo file_get_contents('assets/admin.svg' ); ?>
                            <span class="menuTxt">Admin</span></a>


                        <button id="magicButton">
                            <?php echo file_get_contents('assets/plus.svg' ); ?>
                            <strong>Nouveau post</strong>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebarMobile">
            <div class="sidebarMobileContainer">
                <a href="/" aria-label="Accueil" class="menuLink"> <?php echo file_get_contents('assets/home.svg' ); ?> </a>
                <a href="/search" aria-label="Rechercher" class="menuLink"> <?php echo file_get_contents('assets/wen.svg' ); ?> </a>
                <a href="/profile" aria-label="Profile" class="menuLink"> <?php echo file_get_contents('assets/profile.svg' ); ?> </a>
                <a href="/admin" aria-label="Admin" class="menuLink"> <?php echo file_get_contents('assets/admin.svg' ); ?> </a>
            </div>
        </div>

        <?php
        $sidebar = ob_get_clean();
        return $sidebar;
    }
}
