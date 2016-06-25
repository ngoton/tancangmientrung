<?php
Class albumController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2 ) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý album ảnh';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'album_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'DESC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
        }

        

        $album_model = $this->model->get('albumModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        

        
        $tongsodong = count($album_model->getAllAlbum());
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
            $search = '( album_name LIKE "%'.$keyword.'%")';
            $data['where'] = $search;
        }
        $albums = $album_model->getAllAlbum($data);
        $this->view->data['albums'] = $albums;
        

        $this->view->show('album/index');
    }

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 2) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['title'] = 'Thêm album ảnh';
        $album = $this->model->get('albumModel');
        $image = $this->model->get('imageModel');
        /*Thêm vào CSDL*/
        if (isset($_POST['submit'])) {
            if ($_POST['album_name'] != '') {
                
                $r = $album->getAlbumByWhere(array('album_name'=>trim($_POST['album_name'])));
                
                if (!$r) {

                    $data = array(
                        'album_name' => trim($_POST['album_name']),
                        );
                    $album->createAlbum($data);

                    $last_album = $album->getLastAlbum()->album_id;

                    if ($_FILES['image_link'] != null) {
                        $name = array();
                        $tmp_name = array();
                        $error = array();
                        $ext = array();
                        $size = array();
                        foreach ($_FILES['image_link']['name'] as $file) {
                            $name[] = $file;                            
                        }
                        foreach ($_FILES['image_link']['tmp_name'] as $file){
                            $tmp_name[] = $file;
                        }
                        foreach ($_FILES['image_link']['error'] as $file){
                            $error[] = $file;
                        }
                        foreach ($_FILES['image_link']['type'] as $file){
                            $ext[] = $file;
                        }
                        foreach ($_FILES['image_link']['size'] as $file){
                            $size[] = $file;
                        } //Phần này lấy giá trị ra từng mảng nhỏ

                        for ($i=0;$i<count($name);$i++){
                            if ($error[$i] > 0)
                              {
                              $this->view->data['error'] = "Lỗi ". $error[$i];
                              }
                            else if ($ext[$i] != "image/jpeg" && $ext[$i] != "image/png" && $ext[$i] != "image/gif")
                                {
                                    $this->view->data['error'] = "Không hỗ trợ file ". $ext[$i];
                                }
                            else if ($size[$i]> 5000000)
                                {
                                    $this->view->data['error'] = "File không được lớn hơn 5Mb";
                                }
                            else{
                                    $path = "public/images/upload/";
                                    move_uploaded_file($tmp_name[$i], $path.$name[$i]);
                                    $data_img = array(
                                        'image_link' => $name[$i],
                                        'album' => $last_album,
                                        );
                                    $image->createImage($data_img);
                                }
                        }
                        

                        $this->view->data['error'] = "Thêm album mới thành công";
                    }
                }
                else{
                     $this->view->data['error'] = "Tên album này đã tồn tại";
                }
            }
        }
        return $this->view->show('album/add');
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
            $this->view->redirect('album');
        }
        $this->view->data['title'] = 'Cập nhật album ảnh';
        $this->view->data['id_album'] = $id;
        $album = $this->model->get('albumModel');
        $album_data = $album->getAlbum($id);
        
        if (!$album_data) {
            $this->view->redirect('album');
        }
        else {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
                $order = isset($_POST['order']) ? $_POST['order'] : null;
                $page = isset($_POST['page']) ? $_POST['page'] : null;
                $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
                $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            }
            else{
                $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'image_id';
                $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'DESC';
                $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
                $keyword = "";
                $limit = 18446744073709;
            }
            $image = $this->model->get('imageModel');
            $sonews = 15;
            $x = ($page-1) * $sonews;
            $pagination_stages = 2;
            
             $join = array('table'=>'album','where'=>'album.album_id = image.album');
            $data = array(
                'where'=>'album = '.$id,
                );
            $tongsodong = count($image->getAllImage($data,$join));
            $tongsotrang = ceil($tongsodong / $sonews);
            

            $this->view->data['page'] = $page;
            $this->view->data['order_by'] = $order_by;
            $this->view->data['order'] = $order;
            $this->view->data['keyword'] = $keyword;
            $this->view->data['pagination_stages'] = $pagination_stages;
            $this->view->data['tongsotrang'] = $tongsotrang;
            $this->view->data['sonews'] = $sonews;

            $data = array(
                'order_by'=>$order_by,
                'order'=>$order,
                'limit'=>$x.','.$sonews,
                'where'=>'album = '.$id,
                );
            
            if ($keyword != '') {
                $search = '( album_name LIKE "%'.$keyword.'%") AND album = '.$id;
                $data['where'] = $search;
            }
            
            
            $image_data = $image->getAllImage($data,$join);
            $this->view->data['images'] = $image_data;

            /*Thêm vào CSDL*/
            if (isset($_POST['submit'])) {
                if ($_POST['image_title'] != '') {
                    
                    $check = $image->checkImage($id,trim($_POST['image_title']));
                    if(!$check){
                        $data = array(
                            'image_title' => trim($_POST['image_title']),
                            'image_description' => trim($_POST['image_description']),
                            );
                         if ($_FILES['image_image']['name'] != '') {
                            $this->lib->upload_image('image_image');
                            $data['image_image'] = $_FILES['image_image']['name'];
                        }
                        $image->updateImage($data,array('image_id'=>$id));
                        $this->view->data['error'] = "Cập nhật thành công";
                    }
                    else{
                        $this->view->data['error'] = "Hình này đã tồn tại";
                    }
                }
            }
        }
        
        return $this->view->show('album/edit');
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
            $album = $this->model->get('albumModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $album->deleteAlbum($data);
                }
                return true;
            }
            else{
                return $album->deleteAlbum($_POST['data']);
            }
            
        }
    }

    public function view() {
        
        $this->view->show('album/view');
    }

}
?>