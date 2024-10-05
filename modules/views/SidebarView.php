<?php

class SidebarView {

    public function show() {
        ob_start();
        ?>

        <div class="sidebars">
            <div class="sidebar">
                <div class="sidebarFixed">
                    <ul>
                        <div class="menuLink">
                            <?php echo file_get_contents('assets/y.svg' ); ?>
                        </div>
                        <a href="/" class="menuLink"> <?php echo file_get_contents('assets/home.svg' ); ?>
                            <span class="menuTxt">Accueil</span></a>
                        <a href="/search" class="menuLink">
                            <?php echo file_get_contents('assets/wen.svg' ); ?>                            <span class="menuTxt">Recherche</span></a>
                        <a href="/profile" class="menuLink">
                            <?php echo file_get_contents('assets/profile.svg' ); ?>                            <span class="menuTxt">Profil</span></a>
                        <a href="/admin" class="menuLink">
                            <?php echo file_get_contents('assets/admin.svg' ); ?>
                            <span class="menuTxt">Admin</span></a>

                        <div class="magicButton" id="magicButton">
                            <div class="">
                                <div>
                                    <button class="magicButtonBis">
                                        <div class="inner">
                                            <span class="bold">
                                                <?php echo file_get_contents('assets/plus.svg' ); ?>
                                            </span>
                                            <strong>Nouveau post</strong>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </ul>
                </div>
            </div>
        </div>
        <div class="sidebarMobile">
            <ul class="sidebarMobileContainer">
                <a href="" class="menuLink"> <?php echo file_get_contents('assets/home.svg' ); ?> </a>
                <a href="" class="menuLink"> <?php echo file_get_contents('assets/wen.svg' ); ?> </a>
                <a href="" class="menuLink"> <?php echo file_get_contents('assets/profile.svg' ); ?> </a>
                <a href="" class="menuLink"> <?php echo file_get_contents('assets/admin.svg' ); ?> </a>
            </ul>
        </div>

        <?php
        $sidebar = ob_get_clean();
        return $sidebar;
    }
}
