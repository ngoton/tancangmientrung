<?php
Class pageController Extends baseController {
    public function index() {
       
        $this->view->data['lib'] = $this->lib;
        $menu = $this->model->get('menuModel');
        $menus = $menu->getAllMenu();
        $this->view->data['menus'] = $menus;
        $post = $this->model->get('postModel');
        if (isset($this->registry->router->action)) {
            $id = $this->registry->router->action;
            if (!is_numeric($id)) {
                $this->view->redirect('');
            }
            $menu_data = $menu->getMenu($id);
            if (!$menu_data) {
                $this->view->redirect('');
            }
            if ($menu_data->menu_id == 9) {
                $this->view->redirect('page/38');
            }
            $this->view->data['title'] = $menu_data->menu_name;
            $this->view->data['menu'] = $menu_data;
            $this->view->data['menu_info'] = $menu_data;
            if ($menu_data->menu_parent != 0) {
                $this->view->data['menu'] = $menu->getMenuByWhere(array('menu_id'=>$menu_data->menu_parent));

                if ($menu_data->menu_id==38) {
                    $image_model = $this->model->get('imageModel');
                    if (!isset($this->registry->router->param_id)) {
                        $album_model = $this->model->get('albumModel');
                        $album = $album_model->getAllAlbum(array('order_by'=>'album_id','order'=>'DESC'));
                        $this->view->data['album'] = $album;

                        

                        $image = array();
                        foreach ($album as $album) {
                            $images = $image_model->getAllImage(array('where'=>'album = '.$album->album_id));
                            foreach ($images as $img) {
                                $image[$album->album_id] = $img->image_link;
                                break;
                            }
                        }
                        
                        $this->view->data['image'] = $image;
                    }
                    
                }
                elseif ($menu_data->menu_id==39) {
                    $video_model = $this->model->get('videoModel');
                    $video = $video_model->getAllVideo(array('order_by'=>'video_id','order'=>'DESC'));
                    $this->view->data['video'] = $video;
                }
                elseif ($menu_data->menu_id==40) {
                    $present_model = $this->model->get('presentationModel');
                    $present = $present_model->getAllPresentation(array('order_by'=>'presentation_id','order'=>'DESC'));
                    $this->view->data['present'] = $present;
                }
                elseif ($menu_data->menu_id==41) {
                    $brochure_model = $this->model->get('brochureModel');
                    $brochure = $brochure_model->getAllBrochure(array('order_by'=>'brochure_id','order'=>'DESC'));
                    $this->view->data['brochure'] = $brochure;
                }
            }
            

            $menu_list = $menu->getAllMenu(array('where'=>'menu_parent = '.$id));
            if (!$menu_list) {
                $menu_list = $menu->getAllMenu(array('where'=>'menu_parent != 0 AND menu_parent = '.$menu_data->menu_parent));
                
            }

            $post_menu = array();
            

            foreach ($menu_list as $menu_post) {
                $data =array(
                'where' => 'menu = '.$menu_post->menu_id,
                'order_by'=> 'post_id',
                'order'=> 'DESC',
                );
                $post_menu[$menu_post->menu_id] = $post->getAllPost($data);
            }
            $this->view->data['post_menu'] = $post_menu;
            

            $this->view->data['menu_list'] = $menu_list;
            

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
                $order = isset($_POST['order']) ? $_POST['order'] : null;
                $page = isset($_POST['page']) ? $_POST['page'] : null;
                $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            }
            else{
                $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'post_id';
                $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'DESC';
                $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
                $limit = 18446744073709;
            }

            $sonews = 5;
            $x = ($page-1) * $sonews;
            $pagination_stages = 2;

            $data = array(
                'where'=>'menu = '.$menu_data->menu_id
                );
            
            $tongsodong = count($post->getAllPost($data));
            $tongsotrang = ceil($tongsodong / $sonews);
            

            $this->view->data['page'] = $page;
            $this->view->data['order_by'] = $order_by;
            $this->view->data['order'] = $order;
            $this->view->data['pagination_stages'] = $pagination_stages;
            $this->view->data['tongsotrang'] = $tongsotrang;
            $this->view->data['sonews'] = $sonews;

            $data = array(
                'where'=>'menu = '.$menu_data->menu_id,
                'order_by'=>$order_by,
                'order'=>$order,
                'limit'=>$x.','.$sonews,
                );



            $post_list = $post->getAllPost($data);


            $this->view->data['post'] = $post_list;
        }
        if (isset($this->registry->router->param_id)) {
            $post_id = $this->registry->router->param_id;
            
            if ($menu_data->menu_id==38) {
                 $join = array('table'=>'album','where'=>'album.album_id = image.album');
                $image_data = $image_model->getAllImage(array('where'=>'album = '.$post_id),$join);
                $this->view->data['image_data'] = $image_data;
                
            }
            else{
                $post_data = $post->getPostByWhere(array('link'=>$post_id));
                if (!$post_data) {
                    $this->view->redirect('page/'.$id);
                }
                $this->view->data['title'] = $post_data->post_title;
                $this->view->data['post_data'] = $post_data;
                
                $data =array(
                    'where' => '(menu = '.$post_data->menu.' AND post_id != '.$post_data->post_id.')',
                    'order_by'=> 'post_id',
                    'order'=> 'DESC',
                    'limit'=> 10,
                    );

                $post_relative = $post->getAllPost($data);

                $this->view->data['post_relative'] = $post_relative;
            }
        }
        


        $this->view->show('page/index');
    }

    

}
?>