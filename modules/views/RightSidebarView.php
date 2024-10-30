<?php

class RightSidebarView {
    /**
     * @return string
     */
    public static function show() {
        ob_start();
        ?>

        <div class="rightSidebar">
        </div>

        <?php
        $sidebar = ob_get_clean();
        return $sidebar;
    }
}
