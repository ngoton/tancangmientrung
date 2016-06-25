<?php
Class videoController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2 ) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý video';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'video_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'DESC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
        }

        

        $video_model = $this->model->get('videoModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        

        
        $tongsodong = count($video_model->getAllVideo());
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
            $search = '( video_title LIKE "%'.$keyword.'%" OR video_description LIKE "%'.$keyword.'%")';
            $data['where'] = $search;
        }
        $videos = $video_model->getAllVideo($data);
        $this->view->data['videos'] = $videos;
        

        $this->view->show('video/index');
    }

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['title'] = 'Thêm video';
        
        $video = $this->model->get('videoModel');
        /*Thêm vào CSDL*/
        if (isset($_POST['submit'])) {
            if ($_POST['video_title'] != '' && $_FILES['video_link']['name'] != '') {

                $r = $video->getVideoByWhere(array('video_title'=>trim($_POST['video_title'])));
                
                if (!$r) {
                    $data = array(
                        'video_title' => trim($_POST['video_title']),
                        'video_description' => trim($_POST['video_description']),
                        );
                    if ($_FILES['video_link']['name'] != '') {
                            $this->lib->upload_file('video_link');
                            $data['video_link'] = $_FILES['video_link']['name'];
                        }
                    $video->createVideo($data);

                    

                    $this->view->data['error'] = "Thêm mới thành công";
                }
                else{
                     $this->view->data['error'] = "Video đã tồn tại";
                }
            }
        }
        return $this->view->show('video/add');
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
            $this->view->redirect('video');
        }
        $this->view->data['title'] = 'Cập nhật video';
        $video = $this->model->get('videoModel');
        $video_data = $video->getVideo($id);
        
        if (!$video_data) {
            $this->view->redirect('video');
        }
        else {
            
           $this->view->data['video'] = $video_data;
            /*Thêm vào CSDL*/
            if (isset($_POST['submit'])) {
                if ($_POST['video_title'] != '') {
                    
                    $check = $video->checkVideo($id,trim($_POST['video_title']));
                    if(!$check){
                        $data = array(
                            'video_title' => trim($_POST['video_title']),
                            'video_description' => trim($_POST['video_description']),
                            );
                    
                        $video->updateVideo($data,array('video_id'=>$id));
                        $this->view->data['error'] = "Cập nhật thành công";
                    }
                    else{
                        $this->view->data['error'] = "Video đã tồn tại";
                    }
                }
            }
        }
        
        return $this->view->show('video/edit');
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
            $video = $this->model->get('videoModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $link = $video->getVideo($data);
                    unlink('public/files/'.$link->video_link);
                    $video->deleteVideo($data);
                }
                return true;
            }
            else{
                $link = $video->getVideo($_POST['data']);
                    unlink('public/files/'.$link->video_link);
                return $video->deleteVideo($_POST['data']);
            }
            
        }
    }

    public function view() {
        
        $this->view->show('video/view');
    }

}
?>