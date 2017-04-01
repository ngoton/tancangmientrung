<?php
Class indexController Extends baseController {
    public function index() {
            $menu_model = $this->model->get('menuModel');
            $menus = $menu_model->getAllMenu();
            $this->view->data['menus'] = $menus;
            $this->view->data['title'] = 'Logistics, Vận tải đường bộ, đường biển, Thủ tục hải quan, Container, Xuất nhập khẩu tại Quy Nhơn, Bình Định';

            $post_model = $this->model->get('postModel');
            $data = array(
                'where' => '( menu_parent = 2 OR menu = 3 )',
                'order_by' => 'post_id',
                'order' => 'DESC',
                'limit' => 7,
                );
            $join = array('table'=>'menu','where'=>'post.menu = menu.menu_id');
            $posts = $post_model->getAllPost($data,$join);
            $this->view->data['posts'] = $posts;

            $data = array(
                'where' => '( menu_parent = 2 OR menu = 3 )',
                'order_by' => 'RAND()',
                'limit' => 5,
                );
            $post_features = $post_model->getAllPost($data,$join);
            $this->view->data['post_features'] = $post_features;

            $this->view->show('index');
    }

    public function view() {
        /*** set a template variable ***/
            $this->view->data['view'] = 'hehe';
        /*** load the index template ***/
            $this->view->show('index/view');
    }

}
?>