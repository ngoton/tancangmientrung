<?php
Class chilipowderController Extends baseController {
    public function index() {
        
            $menu_model = $this->model->get('menuModel');
            $menus = $menu_model->getAllMenu();
            $this->view->data['menus'] = $menus;
            $this->view->data['title'] = 'Chili Powder';


            $this->view->show('chilipowder/index');
    }


}
?>