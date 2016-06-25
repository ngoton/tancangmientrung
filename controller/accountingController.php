<?php
Class accountingController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 4) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý chi tiền';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
            $xe = isset($_POST['xe']) ? $_POST['xe'] : null;
            $trangthai = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'shipment_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            $batdau = date('d-m-Y',strtotime("-1 month"));
            $ketthuc = date('d-m-Y',strtotime("+1 day")); //cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).'-'.date('m-Y');
            $xe = "";
            $trangthai = 0;
        }

        $driver_model = $this->model->get('driverModel');
        


        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();

        $this->view->data['vehicles'] = $vehicles;

        $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');

        $shipment_model = $this->model->get('shipmentModel');
        $sonews = 50;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;

        $data = array(
            'where' => "(shipment_sub IS NULL OR shipment_sub != 1)",
            );
        if($batdau != "" && $ketthuc != "" ){
            $data['where'] = $data['where'].' AND shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc);
        }
        if($xe != ""){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }

        if($trangthai != -1){
            $data['where'] = $data['where'].' AND shipment_pay = '.$trangthai;
        }

        /*if ($_SESSION['role_logined'] == 3) {
            $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];
            
        }*/

        $tongsodong = count($shipment_model->getAllShipment($data,$join));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['sonews'] = $sonews;

        $this->view->data['batdau'] = $batdau;
        $this->view->data['ketthuc'] = $ketthuc;

        $this->view->data['xe'] = $xe;
        $this->view->data['trangthai'] = $trangthai;


        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => "(shipment_sub IS NULL OR shipment_sub != 1)",
            );
        if($batdau != "" && $ketthuc != "" ){
            $data['where'] = $data['where'].' AND shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc);
        }
        if($xe != ""){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }
        if($trangthai != -1){
            $data['where'] = $data['where'].' AND shipment_pay = '.$trangthai;
        }

        /*if ($_SESSION['role_logined'] == 3) {
            $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];
            
        }*/

        if ($keyword != '') {
            $ngay = (strtotime(str_replace("/", "-", $keyword))!="")?(' OR shipment_date LIKE "%'.strtotime(str_replace("/", "-", $keyword)).'%"'):"";
            $search = '(
                    vehicle_number LIKE "%'.$keyword.'%"
                    OR customer_name LIKE "%'.$keyword.'%"
                    OR shipment_from in (SELECT warehouse_id FROM warehouse WHERE warehouse_name LIKE "%'.$keyword.'%" ) 
                    OR shipment_to in (SELECT warehouse_id FROM warehouse WHERE warehouse_name LIKE "%'.$keyword.'%" ) 
                    '.$ngay.'
                        )';
            $data['where'] = $data['where']." AND ".$search;
        }

        
        
        $warehouse_model = $this->model->get('warehouseModel');
        $road_model = $this->model->get('roadModel');
        
        

        $warehouse_data = array();
        $road_data = array();
        
        
        $this->view->data['shipments'] = $shipment_model->getAllShipment($data,$join);

        $this->view->data['lastID'] = isset($shipment_model->getLastShipment()->shipment_id)?$shipment_model->getLastShipment()->shipment_id:0;

        $v = array();
        $driver_data = array();

        foreach ($shipment_model->getAllShipment($data,$join) as $ship) {

            $d_data = array(
                'where'=> 'end_work > '.$ship->shipment_date.' AND vehicle = '.$ship->vehicle,
            );
            $drivers = $driver_model->getAllDriver($d_data);
            
            foreach ($drivers as $driver) {
                $driver_data[$ship->shipment_id]['driver_name'] = $driver->driver_name;
                $driver_data[$ship->shipment_id]['driver_phone'] = $driver->driver_phone;
            }

            

            $month = date('m',$ship->shipment_date);
            if(date('d',$ship->shipment_date)>29){
                $month = strval(date('m',$ship->shipment_date)+1);
            }

           $v[$ship->vehicle_id.$ship->shipment_round.$month] = isset($v[$ship->vehicle_id.$ship->shipment_round.$month])?($v[$ship->vehicle_id.$ship->shipment_round.$month] + 1) : (0+1) ;

           $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$ship->shipment_from.' AND road_to = '.$ship->shipment_to.' AND start_time <= '.$ship->shipment_date.' AND end_time >= '.$ship->shipment_date));
            
           $road_data['oil_add'][$ship->shipment_id] = ($ship->oil_add_dc == 5)?$ship->oil_add:0;
           $road_data['oil_add2'][$ship->shipment_id] = ($ship->oil_add_dc2 == 5)?$ship->oil_add2:0;

            $chek_rong = 0;
            
            foreach ($roads as $road) {
                $road_data['bridge_cost'][$ship->shipment_id] = $road->bridge_cost;
                $road_data['police_cost'][$ship->shipment_id] = $road->police_cost;
                $road_data['tire_cost'][$ship->shipment_id] = $road->tire_cost;
                $road_data['oil_cost'][$ship->shipment_id] = $road->road_oil*$ship->oil_cost;

                $chek_rong = ($road->way == 0)?1:0;

            }

            $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$ship->shipment_from.' OR warehouse_code = '.$ship->shipment_to.') AND start_time <= '.$ship->shipment_date.' AND end_time >= '.$ship->shipment_date));
        

            $boiduong_cont = 0;
            $boiduong_tan = 0;

            
            foreach ($warehouse as $warehouse) {
                
                    $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;
                    $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name;

                    $tan = explode(".",$ship->shipment_ton);
                    if (isset($tan[1]) && substr($tan[1], 0, 1) > 5 ) {
                        $trongluong = $tan[0] + 1;
                    }
                    elseif (isset($tan[1]) && substr($tan[1], 0, 1) < 5 ) {
                        $trongluong = $tan[0];
                    }
                    else{
                        $trongluong = $tan[0]+('0.'.(isset($tan[1])?substr($tan[1], 0, 1):0));
                    }

                if($chek_rong == 0){
                    if ($warehouse->warehouse_cont != 0) {
                        $boiduong_cont += $warehouse->warehouse_cont;
                    }
                    if ($warehouse->warehouse_ton != 0){
                        $boiduong_tan += $trongluong * $warehouse->warehouse_ton;
                    }
                }
                else{
                    if ($ship->shipment_ton > 0) {
                        $boiduong_cont += $warehouse->warehouse_add;
                    }
                }
                
            }
            $warehouse_data['boiduong_cn'][$ship->shipment_id] = $boiduong_cont+$boiduong_tan;
        }

        $this->view->data['driver_data'] = $driver_data;
        $this->view->data['warehouse'] = $warehouse_data;
        $this->view->data['road'] = $road_data;
        $this->view->data['arr'] = $v;
        $this->view->show('accounting/index');
    }

    public function pay(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 4) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['data'])) {

            $shipment = $this->model->get('shipmentModel');
            $data = array(
                        
                        'shipment_pay' => 1,
                        'not_del'=>1,
                        );
          
            $shipment->updateShipment($data,array('shipment_id' => $_POST['data']));

            date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."pay"."|".$_POST['data']."|shipment|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

            return true;
                    
        }
    }

}
?>