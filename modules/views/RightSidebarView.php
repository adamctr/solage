<?php

class RightSidebarView {
    public static function show() {
        ob_start();
        ?>

        <?php
        $sidebar = ob_get_clean();
        return $sidebar;
    }
}
