<?php
Class tapiocachipsController Extends baseController {
    public function index() {
        
            $menu_model = $this->model->get('menuModel');
            $menus = $menu_model->getAllMenu();
            $this->view->data['menus'] = $menus;
            $this->view->data['title'] = 'Tapioca Chips';


            $this->view->show('tapiocachips/index');
    }


}
?>