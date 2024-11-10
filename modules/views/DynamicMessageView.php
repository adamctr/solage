<?php

class DynamicMessageView {

    static public function getDivMessage($type, $message) {
        ob_start();
        ?>

        <div class="<?= $type ?> dynamicMessage">
            <p><?= $message ?></p>
        </div>

        <?php

        return ob_get_clean();
    }
}
