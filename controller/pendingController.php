<?php
Class pendingController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Chi phí phát sinh';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
            $xe = isset($_POST['xe']) ? $_POST['xe'] : null;
            $vong = isset($_POST['vong']) ? $_POST['vong'] : null;
            $trangthai = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'vehicle_number ASC, shipment_date ASC, ';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'shipment_round ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            if(date('m') == 3)
                $batdau = '01-03-'.date('Y');
            else
                $batdau = '30-'.date('m-Y', strtotime("last month"));
            
            $ketthuc = date('d-m-Y', time()+86400); //cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).'-'.date('m-Y');
            $xe = "";
            $vong = "";
            $trangthai = 0;
        }

        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();

        $this->view->data['vehicles'] = $vehicles;

        $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');

        $shipment_model = $this->model->get('shipmentModel');
        $sonews = 50;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;

        $data = array(
            'where' => 'cost_add >0 AND shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc),
            );
        if($xe != ""){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }
        if($vong != ""){
            $data['where'] = $data['where'].' AND shipment_round = '.$vong;
        }
        if($trangthai != -1){
            $data['where'] = $data['where'].' AND approve = '.$trangthai;
        }


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
        $this->view->data['vong'] = $vong;

        $this->view->data['trangthai'] = $trangthai;


        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => 'cost_add > 0 AND shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc),
            );
        if($xe != ""){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }
        if($vong != ""){
            $data['where'] = $data['where'].' AND shipment_round = '.$vong;
        }
        if($trangthai != -1){
            $data['where'] = $data['where'].' AND approve = '.$trangthai;
        }

        

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

        foreach ($shipment_model->getAllShipment($data,$join) as $ship) {
            $month = date('m',$ship->shipment_date);
            $year = date('Y',$ship->shipment_date);
            if(date('d',$ship->shipment_date)>29){
                $month = strval(date('m',$ship->shipment_date)+1);
            }

           $v[$ship->vehicle_id.$ship->shipment_round.$month.$year] = isset($v[$ship->vehicle_id.$ship->shipment_round.$month.$year])?($v[$ship->vehicle_id.$ship->shipment_round.$month.$year] + 1) : (0+1) ;

           $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$ship->shipment_from.' AND road_to = '.$ship->shipment_to.' AND start_time <= '.$ship->shipment_date.' AND end_time >= '.$ship->shipment_date));
            
           $road_data['oil_add'][$ship->shipment_id] = ($ship->oil_add_dc == 5)?$ship->oil_add:0;
           $road_data['oil_add2'][$ship->shipment_id] = ($ship->oil_add_dc2 == 5)?$ship->oil_add2:0;

            $chek_rong = 0;
            
            foreach ($roads as $road) {
                $road_data['bridge_cost'][$ship->shipment_id] = round($road->bridge_cost*1.1);
                $road_data['police_cost'][$ship->shipment_id] = $road->police_cost;
                $road_data['tire_cost'][$ship->shipment_id] = $road->tire_cost;
                $road_data['oil_cost'][$ship->shipment_id] = $road->road_oil*round($ship->oil_cost*1.1);
                $road_data['road_oil'][$ship->shipment_id] = $road->road_oil;

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

        $this->view->data['warehouse'] = $warehouse_data;
        $this->view->data['road'] = $road_data;
        $this->view->data['arr'] = $v;
        $this->view->show('pending/index');
    }

    public function equipment() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý vật tư, thiết bị';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $trangthai = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'equipment_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            $trangthai = 0;
            $batdau = "";
        }

        $tire_model = $this->model->get('tireModel');
        $tires = $tire_model->getAllTire();

        $this->view->data['tires'] = $tires;
        $tire_data = array();
        foreach ($tires as $tire) {
            $tire_data['serie'][$tire->tire_id] = $tire->tire_serie;
        }
        $this->view->data['tire_data'] = $tire_data;


        $join = array('table'=>'vehicle','where'=>'vehicle.vehicle_id = equipment.vehicle');

        $data = array();

        if($trangthai != -1){
            $data['where'] = 'approve = '.$trangthai;
        }
        if($batdau != "" && $trangthai != -1){
            $data['where'] = $data['where'].' AND equipment_date = '.strtotime($batdau);
        }
        else if($batdau != "" && $trangthai == -1){
            $data['where'] = 'equipment_date = '.strtotime($batdau);
        }

        $equipment_model = $this->model->get('equipmentModel');
        $sonews = 15;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $tongsodong = count($equipment_model->getAllEquipment($data,$join));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['sonews'] = $sonews;

        $this->view->data['trangthai'] = $trangthai;
        $this->view->data['batdau'] = $batdau;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            );

        if($trangthai != -1){
            $data['where'] = 'approve = '.$trangthai;
        }
        if($batdau != "" && $trangthai != -1){
            $data['where'] = $data['where'].' AND equipment_date = '.strtotime($batdau);
        }
        else if($batdau != "" && $trangthai == -1){
            $data['where'] = 'equipment_date = '.strtotime($batdau);
        }

        if ($keyword != '') {
            $search = ' AND ( vehicle_number LIKE "%'.$keyword.'%" 
                        OR tire_in in (SELECT tire_id FROM tire WHERE tire_serie LIKE "%'.$keyword.'%") 
                        OR tire_out in (SELECT tire_id FROM tire WHERE tire_serie LIKE "%'.$keyword.'%")
                )';
            $data['where'] = $data['where'].$search;
        }

        
        $this->view->data['equipments'] = $equipment_model->getAllEquipment($data,$join);

        
        
        $this->view->show('pending/equipment');
    }

    public function vehicle() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý vật tư, thiết bị';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $trangthai = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'vehicle_number';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            $trangthai = 0;
            $batdau = "";
        }

        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->queryVehicle('SELECT vehicle_id, vehicle_number, approve, COUNT(tire_out) as total FROM vehicle, equipment WHERE vehicle.vehicle_id = equipment.vehicle AND (approve is null OR approve != 1) GROUP BY vehicle ORDER BY '.$order_by.' '.$order);

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['limit'] = $limit;
        $this->view->data['sonews'] = 100;
        
        $this->view->data['vehicles'] = $vehicles;

        
        
        $this->view->show('pending/vehicle');
    }

    public function supplies() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Mua sắm đội xe';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $trangthai = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'supplies_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
            $trangthai = 0;
        }

        

        $supplies_model = $this->model->get('suppliesModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array();

        if($trangthai == 1){
            $data['where'] = 'approve > 0 ';
        }
        else if($trangthai == 0){
            $data['where'] = 'approve IS NULL ';
        }

        $tongsodong = count($supplies_model->getAllSupplies($data));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['limit'] = $limit;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['sonews'] = $sonews;

        $this->view->data['trangthai'] = $trangthai;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            );

        if($trangthai == 1){
            $data['where'] = 'approve > 0 ';
        }
        else if($trangthai == 0){
            $data['where'] = 'approve IS NULL ';
        }
        
        if ($keyword != '') {
            $search = '( supplies_name LIKE "%'.$keyword.'%" )';
            $data['where'] = $search;
        }
        
        
        
        $this->view->data['supplies'] = $supplies_model->getAllSupplies($data);

        $this->view->data['lastID'] = isset($supplies_model->getLastSupplies()->supplies_id)?$supplies_model->getLastSupplies()->supplies_id:0;
        
        $this->view->show('pending/supplies');
    }


    public function approve(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['data'])) {

            $equipment = $this->model->get('equipmentModel');
            $equipment_data = $equipment->getEquipment($_POST['data']);

            $data = array(
                        
                        'approve' => 1,
                        'user_approve' => $_SESSION['userid_logined'],
                        );
          
            $equipment->updateEquipment($data,array('vehicle' => $_POST['data']));

            date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."approve"."|".$_POST['data']."|equipment|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

            return true;
                    
        }
    }

    function cpexport(){
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        
            $warehouse_model = $this->model->get('warehouseModel');
            $road_model = $this->model->get('roadModel');
            $shipment_model = $this->model->get('shipmentModel');
            $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');
            $data = array(
                'where' => 'cost_add > 0',
            );
            $warehouse_data = array();
            $road_data = array();
            
            
           $shipments = $shipment_model->getAllShipment($data,$join);

            foreach ($shipments as $ship) {
               
               $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$ship->shipment_from.' AND road_to = '.$ship->shipment_to.' AND start_time <= '.$ship->shipment_date.' AND end_time >= '.$ship->shipment_date));
                
               $road_data['oil_add'][$ship->shipment_id] = ($ship->oil_add_dc == 5)?$ship->oil_add:0;
               $road_data['oil_add2'][$ship->shipment_id] = ($ship->oil_add_dc2 == 5)?$ship->oil_add2:0;

                $chek_rong = 0;
                
                foreach ($roads as $road) {
                    $road_data['bridge_cost'][$ship->shipment_id] = round($road->bridge_cost*1.1);
                    $road_data['police_cost'][$ship->shipment_id] = $road->police_cost;
                    $road_data['tire_cost'][$ship->shipment_id] = $road->tire_cost;
                    $road_data['oil_cost'][$ship->shipment_id] = $road->road_oil*round($ship->oil_cost*1.1);
                    $road_data['road_oil'][$ship->shipment_id] = $road->road_oil;

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


            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $objPHPExcel = new PHPExcel();

            

            $index_worksheet = 0; //(worksheet mặc định là 0, nếu tạo nhiều worksheet $index_worksheet += 1)
            $objPHPExcel->setActiveSheetIndex($index_worksheet)
               ->setCellValue('A1', 'STT')
               ->setCellValue('B1', 'Ngày')
               ->setCellValue('C1', 'Xe')
               ->setCellValue('D1', 'Kho đi')
               ->setCellValue('E1', 'Kho đến')
               ->setCellValue('F1', 'Chi phí phát sinh')
               ->setCellValue('G1', 'Nội dung')
               ->setCellValue('H1', 'Trạng thái');
               

            

            
            
            

            if ($shipments) {

                $hang = 2;
                $i=1;

                $kho_data = array();
                foreach ($shipments as $row) {
                    


                    //$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$hang)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
                     $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $hang, $i++)
                        ->setCellValueExplicit('B' . $hang, $this->lib->hien_thi_ngay_thang($row->shipment_date))
                        ->setCellValue('C' . $hang, $row->vehicle_number)
                        ->setCellValue('D' . $hang, $warehouse_data['warehouse_name'][$row->shipment_from])
                        ->setCellValue('E' . $hang, $warehouse_data['warehouse_name'][$row->shipment_to])
                        ->setCellValue('F' . $hang, $row->cost_add)
                        ->setCellValue('G' . $hang, $row->cost_add_comment)
                        ->setCellValue('H' . $hang, $row->approve==1?'Đã chấp nhận':'Chưa chấp nhận');
                     $hang++;


                  }

          }

            $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

            $highestRow ++;


            $objPHPExcel->getActiveSheet()->getStyle('F2:F'.$highestRow)->getNumberFormat()->setFormatCode("#,##0_);[Black](#,##0)");
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(16);
            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);

            // Set properties
            $objPHPExcel->getProperties()->setCreator("TCMT")
                            ->setLastModifiedBy($_SESSION['user_logined'])
                            ->setTitle("Excess Report")
                            ->setSubject("Excess Report")
                            ->setDescription("Excess Report.")
                            ->setKeywords("Excess Report")
                            ->setCategory("Excess Report");
            $objPHPExcel->getActiveSheet()->setTitle("Chi phi phat sinh");

            $objPHPExcel->getActiveSheet()->freezePane('A1');
            $objPHPExcel->setActiveSheetIndex(0);



            

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename= CHI PHÍ PHÁT SINH.xlsx");
            header("Cache-Control: max-age=0");
            ob_clean();
            $objWriter->save("php://output");
        
    }

    

}
?>