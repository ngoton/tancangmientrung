<?php
Class banggiaController Extends baseController {
    public function index() {
        /*** set a template variable ***/
            //$this->view->data['welcome'] = 'Welcome to CAI MEP TRADING !';
        /*** load the index template ***/
        $this->view->setLayout('banggia');
            $menu_model = $this->model->get('menuModel');
            $menus = $menu_model->getAllMenu();
            $this->view->data['menus'] = $menus;
            $this->view->data['title'] = 'Bảng giá dịch vụ vận tải, xuất nhập khẩu, thủ tục hải quan, chỉnh sửa manifest';

            $json_district = file_get_contents('http://cmglogistics.com.vn/quotation/listdistrict');
            $obj_district = json_decode($json_district);

            $json_district_ship = file_get_contents('http://cmglogistics.com.vn/quotation/listshipping');
            $obj_district_ship = json_decode($json_district_ship);

            $json_location = file_get_contents('http://cmglogistics.com.vn/quotation/listlocation');
            $obj_location = json_decode($json_location);

            $json_manifest = file_get_contents('http://cmglogistics.com.vn/quotation/listmanifest');
            $obj_manifest = json_decode($json_manifest);

            $json_port = file_get_contents('http://cmglogistics.com.vn/quotation/listport');
            $obj_port = json_decode($json_port);
            
            $this->view->data['districts'] = $obj_district_ship;
            $str = "";

            $district = $obj_district;
            $location = $obj_location;

            $arr = array();
            foreach ($location as $loc) {
                $arr[$loc->district][] = $loc;
            }
            
            foreach ($district as $districts) {
                $str .= '<option value="" class="option_fix">'.$districts->district_name.'</option>';
                if (isset($arr[$districts->district_id])){
                    foreach ($arr[$districts->district_id] as $locations) {
                        $str .= '<option value="'.$locations->location_id.'">'."&nbsp;".' '.$locations->location_name.'</option>';
                    }
                }
            }
            
            
            $this->view->data['locations'] = $str;

            $mani = $obj_manifest;

            $mani_arr = array();
            foreach ($mani as $manifest) {
                $mani_arr[$manifest->manifest_type][] = $manifest;
            }
            $manifest_1 = $mani_arr[1];
            $manifest_2 = $mani_arr[2];
            
            $this->view->data['type_1'] = $manifest_1;
            $this->view->data['type_2'] = $manifest_2;

            $port = $obj_port;

            $this->view->data['ports'] = $port;


            $this->view->show('banggia/index');
    }

    public function view() {
        /*** set a template variable ***/
            $this->view->data['view'] = 'hehe';
        /*** load the index template ***/
            $this->view->show('index/view');
    }

}
?>