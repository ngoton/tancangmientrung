<?php
Class presentationController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2 ) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý presentation';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'presentation_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'DESC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
        }

        

        $presentation_model = $this->model->get('presentationModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        

        
        $tongsodong = count($presentation_model->getAllPresentation());
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
            $search = '( presentation_title LIKE "%'.$keyword.'%" OR presentation_description LIKE "%'.$keyword.'%")';
            $data['where'] = $search;
        }
        $presentations = $presentation_model->getAllPresentation($data);
        $this->view->data['presentations'] = $presentations;
        

        $this->view->show('presentation/index');
    }

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['title'] = 'Thêm presentation';
        
        $presentation = $this->model->get('presentationModel');
        /*Thêm vào CSDL*/
        if (isset($_POST['submit'])) {
            if ($_POST['presentation_title'] != '' && $_FILES['presentation_link']['name'] != '') {

                $r = $presentation->getPresentationByWhere(array('presentation_title'=>trim($_POST['presentation_title'])));
                
                if (!$r) {
                    $data = array(
                        'presentation_title' => trim($_POST['presentation_title']),
                        'presentation_description' => trim($_POST['presentation_description']),
                        );
                    if ($_FILES['presentation_link']['name'] != '') {
                            $this->lib->upload_file('presentation_link');
                            $data['presentation_link'] = $_FILES['presentation_link']['name'];
                        }
                        if ($_FILES['presentation_image']['name'] != '') {
                            $this->lib->upload_image('presentation_image');
                            $data['presentation_image'] = $_FILES['presentation_image']['name'];
                        }
                    $presentation->createPresentation($data);

                    

                    $this->view->data['error'] = "Thêm mới thành công";
                }
                else{
                     $this->view->data['error'] = "Presentation đã tồn tại";
                }
            }
        }
        return $this->view->show('presentation/add');
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
            $this->view->redirect('presentation');
        }
        $this->view->data['title'] = 'Cập nhật presentation';
        $presentation = $this->model->get('presentationModel');
        $presentation_data = $presentation->getPresentation($id);
        
        if (!$presentation_data) {
            $this->view->redirect('presentation');
        }
        else {
            
           $this->view->data['presentation'] = $presentation_data;
            /*Thêm vào CSDL*/
            if (isset($_POST['submit'])) {
                if ($_POST['presentation_title'] != '') {
                    
                    $check = $presentation->checkPresentation($id,trim($_POST['presentation_title']));
                    if(!$check){
                        $data = array(
                            'presentation_title' => trim($_POST['presentation_title']),
                            'presentation_description' => trim($_POST['presentation_description']),
                            );
                         if ($_FILES['presentation_image']['name'] != '') {
                            $this->lib->upload_image('presentation_image');
                            $data['presentation_image'] = $_FILES['presentation_image']['name'];
                        }
                        $presentation->updatePresentation($data,array('presentation_id'=>$id));
                        $this->view->data['error'] = "Cập nhật thành công";
                    }
                    else{
                        $this->view->data['error'] = "Presentation đã tồn tại";
                    }
                }
            }
        }
        
        return $this->view->show('presentation/edit');
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
            $presentation = $this->model->get('presentationModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $link = $presentation->getPresentation($data);
                    unlink('public/images/upload/'.$link->presentation_image);
                    unlink('public/files/'.$link->presentation_link);
                    $presentation->deletePresentation($data);
                }
                return true;
            }
            else{
                $link = $presentation->getPresentation($_POST['data']);
                    unlink('public/images/upload/'.$link->presentation_image);
                    unlink('public/files/'.$link->presentation_link);
                return $presentation->deletePresentation($_POST['data']);
            }
            
        }
    }

    public function view() {
        
        $this->view->show('presentation/view');
    }

}
?>