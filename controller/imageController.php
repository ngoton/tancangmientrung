<?php
Class imageController Extends baseController {
    public function index(){
    }
    public function add($id){
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
        $album = $this->model->get('albumModel');
        $album_data = $album->getAlbum($id);
        
        if (!$album_data) {
            $this->view->redirect('album');
        }
        $this->view->data['album_data'] = $album_data;
        
        /*Thêm vào CSDL*/
        if (isset($_POST['submit'])) {
            if ($_POST['album_name'] != '') {
                $image = $this->model->get('imageModel');
                $r = $album->checkAlbum($id,trim($_POST['album_name']));
                
                if (!$r) {

                    $data = array(
                        'album_name' => trim($_POST['album_name']),
                        );
                    $album->updateAlbum($data,array('album_id'=>$id));


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
                                        'album' => $id,
                                        );
                                    $image->createImage($data_img);
                                }
                        }
                        

                        $this->view->data['error'] = "Cập nhật album thành công";
                    }
                }
                else{
                     $this->view->data['error'] = "Tên album này đã tồn tại";
                }
            }
        }
        return $this->view->show('image/add');
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
            $image = $this->model->get('imageModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $link = $image->getImage($data)->image_link;
                    unlink('public/images/upload/'.$link);
                    $image->deleteImage($data);
                }
                return true;
            }
            else{
                    $link = $image->getImage($_POST['data'])->image_link;
                    unlink('public/images/upload/'.$link);
                return $image->deleteImage($_POST['data']);
            }
            
        }
    }


}
?>