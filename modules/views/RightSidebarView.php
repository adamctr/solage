<?php

declare(strict_types=1);

/**
 * Barre latérale droite (zone réservée).
 */
class RightSidebarView
{
    /**
     * Rend la barre latérale droite.
     *
     * @return string HTML de la barre latérale droite.
     */
    public static function show()
    {
        ob_start();
        ?>

        <div class="rightSidebar">
        </div>

        <?php
        $sidebar = ob_get_clean();
        return $sidebar;
    }
}
