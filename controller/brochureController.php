<?php
Class brochureController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2 ) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý brochure';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'brochure_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'DESC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
        }

        

        $brochure_model = $this->model->get('brochureModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        

        
        $tongsodong = count($brochure_model->getAllBrochure());
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
            $search = '( brochure_title LIKE "%'.$keyword.'%" OR brochure_description LIKE "%'.$keyword.'%")';
            $data['where'] = $search;
        }
        $brochures = $brochure_model->getAllBrochure($data);
        $this->view->data['brochures'] = $brochures;
        

        $this->view->show('brochure/index');
    }

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['title'] = 'Thêm brochure';
        
        $brochure = $this->model->get('brochureModel');
        /*Thêm vào CSDL*/
        if (isset($_POST['submit'])) {
            if ($_POST['brochure_title'] != '' && $_FILES['brochure_link']['name'] != '') {

                $r = $brochure->getBrochureByWhere(array('brochure_title'=>trim($_POST['brochure_title'])));
                
                if (!$r) {
                    $data = array(
                        'brochure_title' => trim($_POST['brochure_title']),
                        'brochure_description' => trim($_POST['brochure_description']),
                        );
                    if ($_FILES['brochure_link']['name'] != '') {
                            $this->lib->upload_file('brochure_link');
                            $data['brochure_link'] = $_FILES['brochure_link']['name'];
                        }
                        if ($_FILES['brochure_image']['name'] != '') {
                            $this->lib->upload_image('brochure_image');
                            $data['brochure_image'] = $_FILES['brochure_image']['name'];
                        }
                    $brochure->createBrochure($data);

                    

                    $this->view->data['error'] = "Thêm mới thành công";
                }
                else{
                     $this->view->data['error'] = "Brochure đã tồn tại";
                }
            }
        }
        return $this->view->show('brochure/add');
    }

    public function edit($id){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2) {
            return $this->view->redirect('user/login');
        }
        if (!$id) {
            $this->view->redirect('brochure');
        }
        $this->view->data['title'] = 'Cập nhật brochure';
        $brochure = $this->model->get('brochureModel');
        $brochure_data = $brochure->getBrochure($id);
        
        if (!$brochure_data) {
            $this->view->redirect('brochure');
        }
        else {
            
           $this->view->data['brochure'] = $brochure_data;
            /*Thêm vào CSDL*/
            if (isset($_POST['submit'])) {
                if ($_POST['brochure_title'] != '') {
                    
                    $check = $brochure->checkBrochure($id,trim($_POST['brochure_title']));
                    if(!$check){
                        $data = array(
                            'brochure_title' => trim($_POST['brochure_title']),
                            'brochure_description' => trim($_POST['brochure_description']),
                            );
                         if ($_FILES['brochure_image']['name'] != '') {
                            $this->lib->upload_image('brochure_image');
                            $data['brochure_image'] = $_FILES['brochure_image']['name'];
                        }
                        $brochure->updateBrochure($data,array('brochure_id'=>$id));
                        $this->view->data['error'] = "Cập nhật thành công";
                    }
                    else{
                        $this->view->data['error'] = "Brochure đã tồn tại";
                    }
                }
            }
        }
        
        return $this->view->show('brochure/edit');
    }

    public function delete(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $brochure = $this->model->get('brochureModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $link = $brochure->getBrochure($data);
                    unlink('public/images/upload/'.$link->brochure_image);
                    unlink('public/files/'.$link->brochure_link);
                    $brochure->deleteBrochure($data);
                }
                return true;
            }
            else{
                $link = $brochure->getBrochure($_POST['data']);
                    unlink('public/images/upload/'.$link->brochure_image);
                    unlink('public/files/'.$link->brochure_link);
                return $brochure->deleteBrochure($_POST['data']);
            }
            
        }
    }

    public function view() {
        
        $this->view->show('brochure/view');
    }

}
?>