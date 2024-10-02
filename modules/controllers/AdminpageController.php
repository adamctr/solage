<?php

class AdminpageController{
    protected $admins; //récupération protégée de la liste des admin récupérer de l'adminmodel
    protected $roles; //pareil avec rôles

    public function __construct(){
        $adminModel = new AdminModel(); //intialisation du model
        $roleModel = new RoleModel();

        $this->admins = $adminModel->getAdmins(); //récupère les admin

        $this->roles = $roleModel->getRoles();
    }
    public function execute(){ //appel la vue
        $view = new AdminView($this->admins, $this->roles);
        $view->show(); //affiche les données
    }
}