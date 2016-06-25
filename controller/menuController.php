<?php
Class menuController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý danh mục';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'menu_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
        }

        

        $menu_model = $this->model->get('menuModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        

        
        $tongsodong = count($menu_model->getAllMenu());
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['limit'] = $limit;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['sonews'] = $sonews;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            );
        
        if ($keyword != '') {
            $search = '( menu_name LIKE "%'.$keyword.'%" )';
            $data['where'] = $search;
        }
        $menus = $menu_model->getAllMenu($data);
        $this->view->data['menus'] = $menus;
        $menu_data = array();
        foreach ($menu_model->getAllMenu() as $menu) {
            $menu_data[$menu->menu_id] = $menu->menu_name;
        }
        //echo json_encode($menu_data);
        $this->view->data['menu_data'] = $menu_data;

        $this->view->show('menu/index');
    }

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['title'] = 'Thêm danh mục';
        
        $menu = $this->model->get('menuModel');
        $this->view->data['menu_parent'] = $menu->getAllMenu();
        /*Thêm vào CSDL*/
        if (isset($_POST['submit'])) {
            if ($_POST['menu_name'] != '' ) {

                $r = $menu->getMenuByWhere(array('menu_name'=>trim($_POST['menu_name']),'menu_parent'=>trim($_POST['menu_parent'])));
                
                if (!$r) {
                    $data = array(
                        'menu_name' => trim($_POST['menu_name']),
                        'menu_parent' => trim($_POST['menu_parent']),
                        );
                    $menu->createMenu($data);

                    

                    $this->view->data['error'] = "Thêm mới thành công";
                }
                else{
                     $this->view->data['error'] = "Tên danh mục đã tồn tại";
                }
            }
        }
        return $this->view->show('menu/add');
    }

    public function edit($id){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        if (!$id) {
            $this->view->redirect('menu');
        }
        $this->view->data['title'] = 'Cập nhật danh mục';
        $menu = $this->model->get('menuModel');
        $menu_data = $menu->getMenu($id);
        
        if (!$menu_data) {
            $this->view->redirect('menu');
        }
        else {
            
            $this->view->data['menu'] = $menu_data;
            
            $menu_parent_old = $menu->getMenu($menu_data->menu_parent);
            if (!$menu_parent_old) {
                $menu_parent_old = new stdClass();
                $menu_parent_old->menu_id = 0;
                $menu_parent_old->menu_name = "-------Không-------";
            }
            $this->view->data['menu_parent_old'] = $menu_parent_old;
            $this->view->data['menu_parent'] = $menu->getAllMenuByWhere($menu_parent_old->menu_id." AND menu_id != ".$menu_data->menu_id);
            /*Thêm vào CSDL*/
            if (isset($_POST['submit'])) {
                if ($_POST['menu_name'] != '') {
                    
                    $check = $menu->checkMenu($id,trim($_POST['menu_name']),trim($_POST['menu_parent']));
                    if(!$check){
                        $data = array(
                            'menu_name' => trim($_POST['menu_name']),
                            'menu_parent' => trim($_POST['menu_parent']),
                            );
                    
                        $menu->updateMenu($data,array('menu_id'=>$id));
                        $this->view->data['error'] = "Cập nhật thành công";
                    }
                    else{
                        $this->view->data['error'] = "Tên danh mục đã tồn tại";
                    }
                }
            }
        }
        
        return $this->view->show('menu/edit');
    }

    public function delete(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $menu = $this->model->get('menuModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $menu->deleteMenu($data);
                }
                return true;
            }
            else{
                return $menu->deleteMenu($_POST['data']);
            }
            
        }
    }

    public function view() {
        
        $this->view->show('menu/view');
    }

}
?>