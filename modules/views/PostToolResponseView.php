<?php

class PostToolResponseView {

    static public function show($post) {
        ob_start();
        ?>

        <?php echo file_get_contents('assets/response.svg' ); ?>
        <span class="menuTxt"><?= $post->getResponsesCount() ?></span>


        <?php
        $responseView = ob_get_clean();
        return $responseView;
        //new LayoutView('Réponse à !!USER!!', 'Page du post et des réponses de l\'utilisateur', $responseView);
    }
}
