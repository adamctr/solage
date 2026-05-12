<?php

class DynamicMessageView {

    static public function getDivMessage($type, $message) {
        ob_start();
        ?>

        <div class="<?= Utils::e($type) ?> dynamicMessage">
            <p><?= Utils::e($message) ?></p>
        </div>

        <?php

        return ob_get_clean();
    }
}
