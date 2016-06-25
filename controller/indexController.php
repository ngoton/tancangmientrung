<?php
Class indexController Extends baseController {
    public function index() {
        /*** set a template variable ***/
            //$this->view->data['welcome'] = 'Welcome to CAI MEP TRADING !';
        /*** load the index template ***/
            $menu_model = $this->model->get('menuModel');
            $menus = $menu_model->getAllMenu();
            $this->view->data['menus'] = $menus;
            $this->view->data['title'] = 'Dịch vụ vận tải, xuất nhập khẩu, thủ tục hải quan, chỉnh sửa manifest';

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