<?php

class SidebarView {

    public function show() {
        ob_start();
        ?>

        <div class="sidebars">
            <div class="sidebar">
                <div class="sidebarFixed">
                    <ul>
                        <div class="menuLink"><img class="sidebarIMG" src="/assets/y.svg" alt="logo"></div>
                        <a href="" class="menuLink"> <img class="sidebarIMG" src="/assets/home.svg" alt=""><span class="menuTxt">Accueil</span></a>
                        <a href="" class="menuLink"> <img class="sidebarIMG" src="/assets/wen.svg" alt=""><span class="menuTxt">Recherche</span></a>
                        <a href="" class="menuLink"> <img class="sidebarIMG" src="/assets/profile.svg" alt=""><span class="menuTxt">Profil</span></a>
                        <a href="" class="menuLink"> <img class="sidebarIMG" src="/assets/admin.svg" alt=""><span class="menuTxt">Admin</span></a>

                        <div class="magicButton">
                            <div class="">
                                <div>
                                    <button class="magicButtonBis">
                                        <div class="inner">
                                            <span>&nbsp+&nbsp</span><strong>Nouveau post</strong>
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
                <a href="" class="menuLink"> <img class="sidebarIMG" src="/assets/home.svg" alt=""><span class="menuTxt">Accueil</span></a>
                <a href="" class="menuLink"> <img class="sidebarIMG" src="/assets/wen.svg" alt=""><span class="menuTxt">Recherche</span></a>
                <a href="" class="menuLink"> <img class="sidebarIMG" src="/assets/profile.svg" alt=""><span class="menuTxt">Profil</span></a>
                <a href="" class="menuLink"> <img class="sidebarIMG" src="/assets/admin.svg" alt=""><span class="menuTxt">Admin</span></a>
            </ul>
        </div>

        <?php
        $sidebar = ob_get_clean();
        return $sidebar;
    }
}
