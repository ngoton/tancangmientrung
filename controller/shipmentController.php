<?php

Class shipmentController Extends baseController {

    public function index() {

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8 && $_SESSION['role_logined'] != 4 && $_SESSION['role_logined'] != 6) {

            return $this->view->redirect('user/login');

        }

        $this->view->data['lib'] = $this->lib;

        $this->view->data['title'] = 'Quản lý lô hàng';



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

        }

        else{

            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'vehicle_number ASC, shipment_date ASC, ';

            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'shipment_round ASC';

            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;

            $keyword = "";

            $limit = 50;



            if(date('m') == 3)

                $batdau = '01-03-'.date('Y');

            else

                $batdau = '30-'.date('m-Y', strtotime("last month"));



            $ketthuc = '29-'.date('m-Y'); //cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).'-'.date('m-Y');



            if (date('d') > 29) {

                $batdau = '30-'.date('m-Y');

                $ketthuc = '29-'.date('m-Y', strtotime("next month"));

            }



            $xe = 0;

            $vong = 0;

        }



        $warehouse_model = $this->model->get('warehouseModel');



        $warehouse_data = array();



        $giadauhientai = $this->craw();

        $this->view->data['giadauhientai'] = $giadauhientai;



        $vehicle_model = $this->model->get('vehicleModel');

        $vehicles = $vehicle_model->getAllVehicle();



        $this->view->data['vehicles'] = $vehicles;



        $shipment_temp_model = $this->model->get('shipmenttempModel');

        $join = array('table'=>'customer, marketing','where'=>'marketing.marketing_id = shipment_temp.marketing AND customer.customer_id = marketing.customer');

        $shipment_temps = $shipment_temp_model->getAllShipment(array('where'=>'shipment_temp_status=0 AND owner='.$_SESSION['userid_logined']),$join);



        foreach ($shipment_temps as $shipment_data) {

            $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$shipment_data->marketing_from.' OR warehouse_code = '.$shipment_data->marketing_to.') AND start_time <= '.$shipment_data->marketing_date.' AND end_time >= '.$shipment_data->marketing_date));

    

            foreach ($warehouse as $warehouse) {

                    $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;

                    $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name; 

            }



        }



        $this->view->data['shipment_temps'] = $shipment_temps;



        $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');



        $shipment_model = $this->model->get('shipmentModel');

        $sonews = $limit;

        $x = ($page-1) * $sonews;

        $pagination_stages = 2;



        $data = array(

            'where' => 'shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc),

            );



        if ($_SESSION['role_logined'] == 3) {

            if( (strtotime($batdau) < strtotime('04-06-2015')) && (strtotime($ketthuc) > strtotime('04-06-2015')) )

                $data['where'] = '( (shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime('04-06-2015').') OR (shipment_date > '.strtotime('04-06-2015').' AND shipment_date <= '.strtotime($ketthuc).' AND shipment_create_user = '.$_SESSION['userid_logined'].') )';

            else if( strtotime($batdau) > strtotime('04-06-2015') )

                $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];

        }





        if($xe != 0){

            $data['where'] = $data['where'].' AND vehicle = '.$xe;

        }

        if($vong != 0){

            $data['where'] = $data['where'].' AND shipment_round = '.$vong;

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

        $this->view->data['limit'] = $limit;

        $this->view->data['pagination_stages'] = $pagination_stages;

        $this->view->data['tongsotrang'] = $tongsotrang;

        $this->view->data['sonews'] = $sonews;



        $this->view->data['batdau'] = $batdau;

        $this->view->data['ketthuc'] = $ketthuc;



        $this->view->data['xe'] = $xe;

        $this->view->data['vong'] = $vong;





        $data = array(

            'order_by'=>$order_by,

            'order'=>$order,

            'limit'=>$x.','.$sonews,

            'where' => 'shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc),

            );



        if ($_SESSION['role_logined'] == 3) {

            if( (strtotime($batdau) < strtotime('04-06-2015')) && (strtotime($ketthuc) > strtotime('04-06-2015')) )

                $data['where'] = '( (shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime('04-06-2015').') OR (shipment_date > '.strtotime('04-06-2015').' AND shipment_date <= '.strtotime($ketthuc).' AND shipment_create_user = '.$_SESSION['userid_logined'].') )';

            else if( strtotime($batdau) > strtotime('04-06-2015') )

                $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];

        }



        if($xe != 0){

            $data['where'] = $data['where'].' AND vehicle = '.$xe;

        }

        if($vong != 0){

            $data['where'] = $data['where'].' AND shipment_round = '.$vong;

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



        $road_model = $this->model->get('roadModel');

       

        $road_data = array();

        

        $datas = $shipment_model->getAllShipment($data,$join);



        $this->view->data['shipments'] = $datas;



        $this->view->data['lastID'] = isset($shipment_model->getLastShipment()->shipment_id)?$shipment_model->getLastShipment()->shipment_id:0;



        $v = array();



        foreach ($datas as $ship) {

            $month = intval(date('m',$ship->shipment_date));

            $year = date('Y',$ship->shipment_date);

            if(date('d',$ship->shipment_date)>29){

                $month = intval(date('m',$ship->shipment_date)+1);
                if ($month == 13) {
                    $month = 1;
                    $year = $year+1;
                }
                

            }



           $v[$ship->vehicle.$ship->shipment_round.$month.$year] = isset($v[$ship->vehicle.$ship->shipment_round.$month.$year])?($v[$ship->vehicle.$ship->shipment_round.$month.$year] + 1) : (0+1) ;



           $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$ship->shipment_from.' AND road_to = '.$ship->shipment_to.' AND start_time <= '.$ship->shipment_date.' AND end_time >= '.$ship->shipment_date));

            

           $road_data['oil_add'][$ship->shipment_id] = ($ship->oil_add_dc == 5)?$ship->oil_add:0;

           $road_data['oil_add2'][$ship->shipment_id] = ($ship->oil_add_dc2 == 5)?$ship->oil_add2:0;



           $check_sub = 1;

           if ($ship->shipment_sub==1) {

               $check_sub = 0;

           }



            $chek_rong = 0;

            

            foreach ($roads as $road) {

                $road_data['bridge_cost'][$ship->shipment_id] = (round($road->bridge_cost*1.1))*$check_sub;

                $road_data['police_cost'][$ship->shipment_id] = ($road->police_cost)*$check_sub;

                $road_data['tire_cost'][$ship->shipment_id] = ($road->tire_cost)*$check_sub;

                $road_data['oil_cost'][$ship->shipment_id] = ($road->road_oil*round($ship->oil_cost*1.1))*$check_sub;

                $road_data['road_oil'][$ship->shipment_id] = ($road->road_oil)*$check_sub;

                $road_data['road_time'][$ship->shipment_id] = ($road->road_time)*$check_sub;



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

            $warehouse_data['boiduong_cn'][$ship->shipment_id] = ($boiduong_cont+$boiduong_tan)*$check_sub;

        }



        $this->view->data['warehouse'] = $warehouse_data;

        $this->view->data['road'] = $road_data;

        $this->view->data['arr'] = $v;

        $this->view->show('shipment/index');

    }



    public function lock(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if (isset($_POST['data'])) {





            $shipment = $this->model->get('shipmentModel');

            $shipment_data = $shipment->getShipment($_POST['data']);



            $data = array(

                        

                        'shipment_complete' => trim($_POST['value']),

                        'not_del' => trim($_POST['value']),

                        );

          

            $shipment->updateShipment($data,array('shipment_id' => $_POST['data']));





            date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."lock"."|".$_POST['data']."|shipment|".$_POST['value']."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);



            return true;

                    

        }

    }



    public function lock_oil(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if (isset($_POST['data'])) {





            $shipment = $this->model->get('shipmentModel');

            $shipment_data = $shipment->getShipment($_POST['data']);



            $data = array(

                        

                        'approve_oil' => trim($_POST['value']),

                        );

          

            $shipment->updateShipment($data,array('shipment_id' => $_POST['data']));





            date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."lock_oil"."|".$_POST['data']."|shipment|".$_POST['value']."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);



            return true;

                    

        }

    }



    public function getshipmenttemp(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if (isset($_POST['data'])) {

            $join = array('table'=>'customer, marketing','where'=>'marketing.marketing_id = shipment_temp.marketing AND customer.customer_id = marketing.customer');



            $shipment = $this->model->get('shipmenttempModel');

            $warehouse_model = $this->model->get('warehouseModel');



            $shipment_datas = $shipment->getAllShipment(array('where'=>'shipment_temp_id='.$_POST['data']),$join);

            foreach ($shipment_datas as $shipment_data) {

                $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$shipment_data->marketing_from.' OR warehouse_code = '.$shipment_data->marketing_to.') AND start_time <= '.$shipment_data->marketing_date.' AND end_time >= '.$shipment_data->marketing_date));

        

                foreach ($warehouse as $warehouse) {

                        $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;

                        $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name; 

                }



                $data = array(   

                    'shipment_temp' => $shipment_data->shipment_temp_id,

                    'shipment_from' => $shipment_data->marketing_from,

                    'shipment_to' => $shipment_data->marketing_to,

                    'shipment_from_name' => $warehouse_data['warehouse_name'][$shipment_data->marketing_from],

                    'shipment_to_name' => $warehouse_data['warehouse_name'][$shipment_data->marketing_to],

                    'customer' => $shipment_data->customer,

                    'customer_name' => $shipment_data->customer_name,

                    'shipment_ton' => $shipment_data->shipment_temp_ton-$shipment_data->shipment_ton_use,

                    'shipment_charge' => $shipment_data->marketing_charge,

                    'commission' => $shipment_data->shipment_temp_commission,

                    'commission_number' => $shipment_data->shipment_temp_commission_number,

                    'shipment_loan' => $shipment_data->shipment_temp_loan,

                    'loan_content' => $shipment_data->shipment_temp_loan_content,

                    );

            }

            

            echo json_encode($data);



            return true;

                    

        }

    }



    public function complete(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if (isset($_POST['data'])) {





            $shipment = $this->model->get('shipmentModel');

            $shipment_data = $shipment->getShipment($_POST['data']);



            $data = array(

                        

                        'shipment_complete' => 1,

                        'not_del' => 1,

                        );

          

            $shipment->updateShipment($data,array('shipment_id' => $_POST['data']));





            date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."complete"."|".$_POST['data']."|shipment|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);



            return true;

                    

        }

    }



    



    public function add(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if (isset($_POST['yes'])) {

            $shipment = $this->model->get('shipmentModel');

            //$ad = mktime(date("H"), date("i"), date("s"), date("m"), date("d")+1, date("Y")); 

            $tomorrow = strtotime(trim($_POST['shipment_date']));

            $data = array(

                        

                        'shipment_from' => trim($_POST['shipment_from']),

                        'shipment_to' => trim($_POST['shipment_to']),

                        'vehicle' => trim($_POST['vehicle']),

                        'customer' => trim($_POST['customer']),

                        'shipment_ton' => trim($_POST['shipment_ton']),

                        'shipment_charge' => trim(str_replace(',','',$_POST['shipment_charge'])),

                        'oil_add' => trim(str_replace(',','',$_POST['oil_add'])),

                        'oil_add2' => trim(str_replace(',','',$_POST['oil_add2'])),

                        'cost_add' => trim(str_replace(',','',$_POST['cost_add'])),

                        'oil_cost' => round((trim(str_replace(',','',$_POST['oil_cost'])))/1.1),

                        'shipment_date' => $tomorrow,

                        'shipment_round' => trim($_POST['shipment_round']),

                        'oil_add_dc' => trim($_POST['oil_add_dc']),

                        'oil_add_dc2' => trim($_POST['oil_add_dc2']),

                        'cost_add_comment' => trim($_POST['cost_add_comment']),

                        'shipment_loan' => trim(str_replace(',','',$_POST['shipment_loan'])),

                        'sub_driver' => trim($_POST['sub_driver']),

                        'sub_driver_name' => trim($_POST['sub_driver_name']),

                        'sub_driver2' => trim($_POST['sub_driver2']),

                        'loan_content' => trim($_POST['loan_content']),

                        'oil_excess' => trim(str_replace(',','',$_POST['oil_excess'])),

                        'oil_excess_comment' => trim($_POST['oil_excess_comment']),

                        'oil_excess_dc' => trim($_POST['oil_excess_dc']),

                        'advance' => trim(str_replace(',','',$_POST['advance'])),

                        'commission' => trim(str_replace(',','',$_POST['commission'])),

                        'advance_comment' => trim($_POST['advance_comment']),

                        'commission_number' => trim($_POST['commission_number']),

                        'oil_residual' => trim(str_replace(',','',$_POST['oil_residual'])),

                        'oil_residual_comment' => trim($_POST['oil_residual_comment']),

                        'cost_vat' => trim(str_replace(',','',$_POST['cost_vat'])),

                        'cost_vat_comment' => trim($_POST['cost_vat_comment']),

                        'cost_excess' => trim(str_replace(',','',$_POST['cost_excess'])),

                        'cost_excess_comment' => trim($_POST['cost_excess_comment']),

                        'shipment_charge_excess' => trim(str_replace(',','',$_POST['shipment_charge_excess'])),

                        'shipment_charge_excess_comment' => trim($_POST['shipment_charge_excess_comment']),

                        'bridge_cost_add' => trim(str_replace(',','',$_POST['bridge_cost_add'])),

                        );



            $road_model = $this->model->get('roadModel');

            $warehouse_model = $this->model->get('warehouseModel');



            $warehouse_datas = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$data['shipment_from'].' OR warehouse_code = '.$data['shipment_to'].') AND start_time <= '.$data['shipment_date'].' AND end_time >= '.$data['shipment_date']));

            $road_datas = $road_model->getAllRoad(array('where'=>'road_from = '.$data['shipment_from'].' AND road_to = '.$data['shipment_to'].' AND start_time <= '.$data['shipment_date'].' AND end_time >= '.$data['shipment_date']));



            if (trim($_POST['shipment_ton_net']) != "") {

                    $data['shipment_ton'] = trim($_POST['shipment_ton_net']);

                    $data['shipment_update'] = 1;

                }



                $boiduong_cont = 0;

                $boiduong_tan = 0;

                $doanhthu = 0;

                $chiphi = 0;

                $loinhuan = 0;



                $chiphi_tam = 0;



                $giadau = round($data['oil_cost']*1.1); //gia dau hien tai



                if ($warehouse_datas) {

                    $tan = explode(".",$data['shipment_ton']);

                    if (isset($tan[1]) && substr($tan[1], 0, 1) > 5 ) {

                        $trongluong = $tan[0] + 1;

                    }

                    elseif (isset($tan[1]) && substr($tan[1], 0, 1) < 5 ) {

                        $trongluong = $tan[0];

                    }

                    else{

                        $trongluong = $tan[0]+('0.'.(isset($tan[1])?substr($tan[1], 0, 1):0));

                    }



                    foreach ($warehouse_datas as $warehouse_data) {

                        if ($warehouse_data->warehouse_cont != 0) {

                            $boiduong_cont += $warehouse_data->warehouse_cont;

                        }

                        if ($warehouse_data->warehouse_ton != 0){

                            $boiduong_tan += $trongluong * $warehouse_data->warehouse_ton;

                        }

                    }

                }

                $boiduong = 0;

                $thuong = 0;

                if ($road_datas) {

                    foreach ($road_datas as $road_data) {

                        $boiduong += ($road_data->way == 0)?0:($boiduong_tan+$boiduong_cont);

                        $chiphi += ($road_data->road_time*3000000)+$boiduong+$road_data->police_cost+round($road_data->bridge_cost*1.1)+$road_data->tire_cost+($road_data->road_oil*$giadau);

                        //$thuong = ($data['shipment_ton']>29.7)?round(($data['shipment_ton']-29.7)+0.4)*$road_data->charge_add:0;

                        $thuong = ($data['shipment_ton']>29)?round($data['shipment_ton']-29)*$road_data->charge_add:0;

                        $chiphi_tam += ($road_data->road_time*3000000)+$boiduong+$road_data->police_cost+round($road_data->bridge_cost*1.1)+$road_data->tire_cost;

                    }

                }



                $chiphi = $chiphi+$data['bridge_cost_add']+$data['shipment_loan']+$data['advance']+($data['commission']*$data['commission_number'])+$data['cost_vat']-$data['cost_excess'];



                if ($data['oil_add_dc'] == 5) {

                    $chiphi_tam = $chiphi_tam+$data['bridge_cost_add']+$data['shipment_loan']+$data['advance']+($data['commission']*$data['commission_number'])+$data['cost_vat']+($data['oil_add']*$giadau);

                }

                else{

                    $chiphi_tam = $chiphi_tam+$data['bridge_cost_add']+$data['shipment_loan']+$data['advance']+($data['commission']*$data['commission_number'])+$data['cost_vat'];

                }



                if ($data['oil_add_dc2'] == 5) {

                    $chiphi_tam = $chiphi_tam+($data['oil_add2']*$giadau);

                }

                



                $doanhthu = $data['shipment_charge']*$data['shipment_ton'];

                $loinhuan = $doanhthu - $chiphi;



                $data['shipment_revenue'] = $doanhthu;

                $data['shipment_cost'] = $chiphi;

                $data['shipment_profit'] = $loinhuan;

                $data['shipment_bonus'] = $thuong;



                $data['shipment_cost_temp'] = $chiphi_tam;



            $result = array();



            if ($_POST['yes'] != "") {

                //$data['shipment_update_user'] = $_SESSION['userid_logined'];

                //$data['shipment_update_time'] = time();

                //var_dump($data);

                $data_shipments = $shipment->getShipment($_POST['yes']);

                $shipment_temp_id = $data_shipments->shipment_temp;

                if($shipment_temp_id > 0){

                    $shipment_temp_model = $this->model->get('shipmenttempModel');

                    $shipment_temp_data = $shipment_temp_model->getShipment($shipment_temp_id);



                    $data_shipment_temp = array(

                        'shipment_ton_use' => $shipment_temp_data->shipment_ton_use-$data_shipments->shipment_ton+$data['shipment_ton'],

                    );



                    $shipment_temp_model->updateShipment($data_shipment_temp,array('shipment_temp_id'=>$shipment_temp_id));



                    $shipment_temp_data = $shipment_temp_model->getShipment($shipment_temp_id);



                    if ( ($shipment_temp_data->shipment_temp_ton-$shipment_temp_data->shipment_ton_use) <= 0) {

                        $shipment_temp_model->updateShipment(array('shipment_temp_status'=>1), array('shipment_temp_id'=>$shipment_temp_id));

                    }

                }

                



                if ($shipment->checkShipment($_POST['yes'],trim($_POST['shipment_from']),trim($_POST['shipment_to']),trim($_POST['vehicle']),$tomorrow,trim($_POST['shipment_round']))) {

                    $result['err'] = "Bảng này đã tồn tại";

                    $result['revenue'] = 0;

                    $result['cost'] = 0;

                    $result['profit'] = 0;

                    echo json_encode($result);

                    return false;

                }

                else{



                        $cptruoc = $shipment->getShipment($_POST['yes'])->shipment_cost_temp;

                        $sodu = $chiphi_tam-$cptruoc;

                    if ($sodu != 0) {



                        $thang = substr($_POST['shipment_date'], 3, 2);

                        $nam = substr($_POST['shipment_date'], 6, 4);



                        $vong = $data['shipment_round']+1;



                        if(substr($_POST['shipment_date'], 0, 2) >= 27 && substr($_POST['shipment_date'], 0, 2) < 30){

                            $vong = 1;

                            if ($thang == 12) {

                                $thang = 1;

                                $nam = $nam+1;

                            }

                            else{

                                $thang = $thang+1;

                            }                            

                        }

                        if(substr($_POST['shipment_date'], 0, 2) >= 30){



                            if ($thang == 12) {

                                $thang = 1;

                                $nam = $nam+1;

                            }

                            else{

                                $thang = $thang+1;

                            }  

                        }



                        if ($shipment->getShipment($_POST['yes'])->shipment_pay != 1) {

                            $sodu = 0;

                        }

                        





                        $excess_model = $this->model->get('excessModel');

                        if ($excess_model->checkExcess($_POST['yes'])) {

                            $ex_data = array(

                                'vehicle' => $data['vehicle'],

                                'round' => $vong,

                                'month' => $thang,

                                'year' => $nam,

                                'excess_cost' => $sodu,

                                'bonus' => $thuong,

                                'used' => 0,

                                'shipment' => $_POST['yes'],

                                );

                            $excess_model->updateExcess($ex_data,array('shipment' => $_POST['yes']));

                        }

                        else{

                            $ex_data = array(

                                'vehicle' => $data['vehicle'],

                                'round' => $vong,

                                'month' => $thang,

                                'year' => $nam,

                                'excess_cost' => $sodu,

                                'bonus' => $thuong,

                                'used' => 0,

                                'shipment' => $_POST['yes'],

                                );

                            $excess_model->createExcess($ex_data);

                        }



                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$excess_model->getLastExcess()->excess_id."|excess|".implode("-",$ex_data)."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);



                        //$data['shipment_excess'] = $sodu;

                    }

                    

                    



                    $shipment->updateShipment($data,array('shipment_id' => $_POST['yes']));

                    $result['err'] =  "Cập nhật thành công";

                    $result['revenue'] = $doanhthu;

                    $result['cost'] = $chiphi;

                    $result['profit'] = $loinhuan;

                    echo json_encode($result);



                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|shipment|".implode("-",$data)."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                    }

            }

            else{

                $data['shipment_create_user'] = $_SESSION['userid_logined'];

                $data['shipment_temp'] = trim($_POST['check_temp']);



                if($data['shipment_temp'] > 0){

                    $shipment_temp_model = $this->model->get('shipmenttempModel');

                    $shipment_temp_data = $shipment_temp_model->getShipment($data['shipment_temp']);



                    $data_shipment_temp = array(

                        'shipment_ton_use' => $shipment_temp_data->shipment_ton_use+$data['shipment_ton'],

                    );



                    $shipment_temp_model->updateShipment($data_shipment_temp,array('shipment_temp_id'=>$data['shipment_temp']));



                    $shipment_temp_data = $shipment_temp_model->getShipment($data['shipment_temp']);



                    if ( ($shipment_temp_data->shipment_temp_ton-$shipment_temp_data->shipment_ton_use) <= 0) {

                        $shipment_temp_model->updateShipment(array('shipment_temp_status'=>1), array('shipment_temp_id'=>$data['shipment_temp']));

                    }

                }

                

                //$data['staff'] = $_POST['staff'];

                //var_dump($data);

                /*if ($shipment->checkUpdate(trim($_POST['vehicle']),trim($_POST['shipment_round']),$tomorrow)) {

                    $result['err'] = "Vui lòng cập nhật trọng tải vòng trước";

                    $result['revenue'] = 0;

                    $result['cost'] = 0;

                    $result['profit'] = 0;

                    echo json_encode($result);

                    return false;

                }*/

                if ($shipment->checkComplete(trim($_POST['vehicle']),trim($_POST['shipment_round']),$tomorrow)) {

                    $result['err'] = "Vui lòng hoàn tất vòng trước";

                    $result['revenue'] = 0;

                    $result['cost'] = 0;

                    $result['profit'] = 0;

                    echo json_encode($result);

                    return false;

                }



                if ($shipment->checkShipment(0,trim($_POST['shipment_from']),trim($_POST['shipment_to']),trim($_POST['vehicle']),$tomorrow,trim($_POST['shipment_round']))) {

                    $result['err'] = "Bảng này đã tồn tại";

                    $result['revenue'] = 0;

                    $result['cost'] = 0;

                    $result['profit'] = 0;

                    echo json_encode($result);

                    return false;

                }

                else{

                    $excess_model = $this->model->get('excessModel');

                    $dk = array(

                        'where' => 'used = 0 AND vehicle = '.$_POST['vehicle'].' AND round != '.$_POST['shipment_round'],

                        );

                    $cp = $excess_model->getAllExcess($dk);

                    $bd_truoc = 0;

                    $thuong_truoc = 0;

                    foreach ($cp as $cp_truoc) {

                        $bd_truoc += $cp_truoc->excess_cost;

                        $thuong_truoc += $cp_truoc->bonus;

                        $excess_model->deleteExcess($cp_truoc->excess_id);

                    }



                    $data['shipment_excess'] = $bd_truoc;

                    //$data['shipment_bonus'] = $thuong_truoc;



                    $data['shipment_cost'] = $chiphi;



                    $data['shipment_cost_temp'] = $chiphi_tam;



                    $data['shipment_update'] = 0;



                    $data['shipment_pay'] = 0;



                    $data['shipment_complete'] = 0;

                    

                    $data['approve'] = 1;

                    if ($data['cost_add'] > 0) {

                        $data['approve'] = 0;

                    }





                    //$shipment->updateShipment(array('not_del'=>1),array('vehicle'=>$data['vehicle'].' AND shipment_date <= '.$data['shipment_date'].' AND shipment_round != '.$data['shipment_round']));

                   

                    $shipment->createShipment($data);

                    $result['err'] = "Thêm thành công";

                    $result['revenue'] = $doanhthu;

                    $result['cost'] = $chiphi;

                    $result['profit'] = $loinhuan;

                    echo json_encode($result);



                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$shipment->getLastShipment()->shipment_id."|shipment|".implode("-",$data)."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                }

                

            }

                    

        }

    }



    public function addsub(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if (isset($_POST['yes'])) {

            $shipment = $this->model->get('shipmentModel');

            //$ad = mktime(date("H"), date("i"), date("s"), date("m"), date("d")+1, date("Y")); 

            $tomorrow = strtotime(trim($_POST['shipment_date']));

            $data = array(

                        

                        'shipment_from' => trim($_POST['shipment_from']),

                        'shipment_to' => trim($_POST['shipment_to']),

                        'vehicle' => trim($_POST['vehicle']),

                        'customer' => trim($_POST['customer']),

                        'shipment_ton' => trim($_POST['shipment_ton']),

                        'shipment_charge' => trim(str_replace(',','',$_POST['shipment_charge'])),

                        'oil_add' => trim(str_replace(',','',$_POST['oil_add'])),

                        'oil_add2' => trim(str_replace(',','',$_POST['oil_add2'])),

                        'cost_add' => trim(str_replace(',','',$_POST['cost_add'])),

                        'oil_cost' => round((trim(str_replace(',','',$_POST['oil_cost'])))/1.1),

                        'shipment_date' => $tomorrow,

                        'shipment_round' => trim($_POST['shipment_round']),

                        'shipment_create_user' => $_SESSION['userid_logined'],

                        'oil_add_dc' => trim($_POST['oil_add_dc']),

                        'oil_add_dc2' => trim($_POST['oil_add_dc2']),

                        'cost_add_comment' => trim($_POST['cost_add_comment']),

                        'shipment_loan' => trim(str_replace(',','',$_POST['shipment_loan'])),

                        'sub_driver' => trim($_POST['sub_driver']),

                        'sub_driver_name' => trim($_POST['sub_driver_name']),

                        'sub_driver2' => trim($_POST['sub_driver2']),

                        'loan_content' => trim($_POST['loan_content']),

                        'shipment_sub' => 1,

                        );





            if (trim($_POST['shipment_ton_net']) != "") {

                    $data['shipment_ton'] = trim($_POST['shipment_ton_net']);

                    $data['shipment_update'] = 1;

                }



                $doanhthu = 0;

                $chiphi = 0;

                $loinhuan = 0;



                $chiphi_tam = 0;



                $giadau = round($data['oil_cost']*1.1); //gia dau hien tai





                $chiphi = $chiphi+$data['shipment_loan'];



                if ($data['oil_add_dc'] == 5) {

                    $chiphi_tam = $chiphi_tam+$data['shipment_loan']+($data['oil_add']*$giadau);

                }

                else{

                    $chiphi_tam = $chiphi_tam+$data['shipment_loan'];

                }



                if ($data['oil_add_dc2'] == 5) {

                    $chiphi_tam = $chiphi_tam+($data['oil_add2']*$giadau);

                }

                



                $doanhthu = $data['shipment_charge']*$data['shipment_ton'];

                $loinhuan = $doanhthu - $chiphi;



                $data['shipment_revenue'] = $doanhthu;

                $data['shipment_cost'] = $chiphi;

                $data['shipment_profit'] = $loinhuan;



                $data['shipment_cost_temp'] = $chiphi_tam;



            $result = array();



            if ($_POST['yes'] != "") {

                //$data['shipment_update_user'] = $_SESSION['userid_logined'];

                //$data['shipment_update_time'] = time();

                //var_dump($data);

                



                if ($shipment->checkShipment($_POST['yes'],trim($_POST['shipment_from']),trim($_POST['shipment_to']),trim($_POST['vehicle']),$tomorrow,trim($_POST['shipment_round']))) {

                    $result['err'] = "Bảng này đã tồn tại";

                    $result['revenue'] = 0;

                    $result['cost'] = 0;

                    $result['profit'] = 0;

                    echo json_encode($result);

                    return false;

                }

                else{





                    $shipment->updateShipment($data,array('shipment_id' => $_POST['yes']));

                    $result['err'] =  "Cập nhật thành công";

                    $result['revenue'] = $doanhthu;

                    $result['cost'] = $chiphi;

                    $result['profit'] = $loinhuan;

                    echo json_encode($result);



                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|shipment|".implode("-",$data)."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                    }

            }

            else{

                //$data['shipment_create_user'] = $_SESSION['userid_logined'];

                //$data['staff'] = $_POST['staff'];

                //var_dump($data);

                /*if ($shipment->checkUpdate(trim($_POST['vehicle']),trim($_POST['shipment_round']),$tomorrow)) {

                    $result['err'] = "Vui lòng cập nhật trọng tải vòng trước";

                    $result['revenue'] = 0;

                    $result['cost'] = 0;

                    $result['profit'] = 0;

                    echo json_encode($result);

                    return false;

                }*/

                if ($shipment->checkComplete(trim($_POST['vehicle']),trim($_POST['shipment_round']),$tomorrow)) {

                    $result['err'] = "Vui lòng hoàn tất vòng trước";

                    $result['revenue'] = 0;

                    $result['cost'] = 0;

                    $result['profit'] = 0;

                    echo json_encode($result);

                    return false;

                }



                if ($shipment->checkShipment(0,trim($_POST['shipment_from']),trim($_POST['shipment_to']),trim($_POST['vehicle']),$tomorrow,trim($_POST['shipment_round']))) {

                    $result['err'] = "Bảng này đã tồn tại";

                    $result['revenue'] = 0;

                    $result['cost'] = 0;

                    $result['profit'] = 0;

                    echo json_encode($result);

                    return false;

                }

                else{



                    $data['shipment_cost'] = $chiphi;



                    $data['shipment_cost_temp'] = $chiphi_tam;



                    $data['shipment_update'] = 0;



                    $data['shipment_pay'] = 0;



                    $data['shipment_complete'] = 0;

                    

                    $data['approve'] = 1;

                    if ($data['cost_add'] > 0) {

                        $data['approve'] = 0;

                    }





                    //$shipment->updateShipment(array('not_del'=>1),array('vehicle'=>$data['vehicle'].' AND shipment_date <= '.$data['shipment_date'].' AND shipment_round != '.$data['shipment_round']));

                   

                    $shipment->createShipment($data);

                    $result['err'] = "Thêm thành công";

                    $result['revenue'] = $doanhthu;

                    $result['cost'] = $chiphi;

                    $result['profit'] = $loinhuan;

                    echo json_encode($result);



                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$shipment->getLastShipment()->shipment_id."|shipment|".implode("-",$data)."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                }

                

            }

                    

        }

    }



    public function oiladd(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $shipment_model = $this->model->get('shipmentModel');

            $road_model = $this->model->get('roadModel');



            $batdau = '30-'.date('m-Y', strtotime("last month"));

            $ketthuc = trim($_POST['ngay']);

            $xe = trim($_POST['xe']);

            $vong = trim($_POST['vong']);



            $total_oil_cost = 0;

            $total_oil_add = 0;



            $total_oil_cost_before = 0;

            $total_oil_add_before = 0;



            $shipments = $shipment_model->getAllShipment(array('where'=>'vehicle = '.$xe.' AND shipment_round = '.$vong.' AND shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.$ketthuc));

            foreach ($shipments as $ship) {

               $total_oil_add += ($ship->oil_add+$ship->oil_add2);



                $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$ship->shipment_from.' AND road_to = '.$ship->shipment_to));

                

                foreach ($roads as $road) {

                    

                    $total_oil_cost += $road->road_oil;

                }

            }



            $shipments = $shipment_model->getAllShipment(array('where'=>'vehicle = '.$xe.' AND shipment_round != '.$vong.' AND shipment_date >= '.strtotime('29-11-2014').' AND shipment_date <= '.$ketthuc));

            foreach ($shipments as $ship) {

               $total_oil_add_before += ($ship->oil_add+$ship->oil_add2);



                $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$ship->shipment_from.' AND road_to = '.$ship->shipment_to));

                

                foreach ($roads as $road) {

                    

                    $total_oil_cost_before += $road->road_oil;

                }

            }



            $oil = $total_oil_cost_before - $total_oil_add_before;



            echo $oil+$total_oil_cost-$total_oil_add;

        }

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



            $shipment = $this->model->get('shipmentModel');

            $shipment_data = $shipment->getShipment($_POST['data']);



            $data = array(

                        

                        'shipment_cost' => trim($shipment_data->cost_add)+trim($shipment_data->shipment_cost),

                        'approve' => 1,

                        'user_approve' => $_SESSION['userid_logined'],

                        );

          

            $shipment->updateShipment($data,array('shipment_id' => $_POST['data']));



            date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."approve"."|".$_POST['data']."|shipment|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);



            return true;

                    

        }

    }





    public function checkroad(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $road_model = $this->model->get('roadModel');

            $road = $road_model->getRoadByWhere(array('road_from' => trim($_POST['road_from']),'road_to' => trim($_POST['road_to'])));



            echo $road->way;

        }

    }



    public function getshipmentfrom(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $warehouse_model = $this->model->get('warehouseModel');

            

            if ($_POST['keyword'] == "*") {

                $data = array(

                'where'=>'( check_new IS NULL )',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            else{

                $data = array(

                'where'=>'( warehouse_name LIKE "'.$_POST['keyword'].'%" AND check_new IS NULL )',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            

            foreach ($list as $rs) {

                // put in bold the written text

                $warehouse_name = $rs->warehouse_name;

                if ($_POST['keyword'] != "*") {

                    $warehouse_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->warehouse_name);

                }

                

                // add new option

                echo '<li onclick="set_item_shipment_from(\''.$rs->warehouse_id.'\',\''.$rs->warehouse_name.'\')">'.$warehouse_name.'</li>';

            }

        }

    }

    public function getshipmentfromsub(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $warehouse_model = $this->model->get('warehouseModel');

            

            if ($_POST['keyword'] == "*") {

                $data = array(

                'where'=>'( check_new IS NULL )',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            else{

                $data = array(

                'where'=>'( warehouse_name LIKE "'.$_POST['keyword'].'%" AND check_new IS NULL )',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            

            foreach ($list as $rs) {

                // put in bold the written text

                $warehouse_name = $rs->warehouse_name;

                if ($_POST['keyword'] != "*") {

                    $warehouse_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->warehouse_name);

                }

                

                // add new option

                echo '<li onclick="set_item_shipment_from_sub(\''.$rs->warehouse_id.'\',\''.$rs->warehouse_name.'\')">'.$warehouse_name.'</li>';

            }

        }

    }



    public function getshipmentto(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $warehouse_model = $this->model->get('warehouseModel');

            

            if ($_POST['keyword'] == "*") {

                $data = array(

                'where'=>'( check_new IS NULL )',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            else{

                $data = array(

                'where'=>'( warehouse_name LIKE "'.$_POST['keyword'].'%" AND check_new IS NULL )',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            

            foreach ($list as $rs) {

                // put in bold the written text

                $warehouse_name = $rs->warehouse_name;

                if ($_POST['keyword'] != "*") {

                    $warehouse_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->warehouse_name);

                }

                

                // add new option

                echo '<li onclick="set_item_shipment_to(\''.$rs->warehouse_id.'\',\''.$rs->warehouse_name.'\')">'.$warehouse_name.'</li>';

            }

        }

    }

    public function getshipmenttosub(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $warehouse_model = $this->model->get('warehouseModel');

            

            if ($_POST['keyword'] == "*") {

                $data = array(

                'where'=>'( check_new IS NULL )',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            else{

                $data = array(

                'where'=>'( warehouse_name LIKE "'.$_POST['keyword'].'%" AND check_new IS NULL )',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            

            foreach ($list as $rs) {

                // put in bold the written text

                $warehouse_name = $rs->warehouse_name;

                if ($_POST['keyword'] != "*") {

                    $warehouse_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->warehouse_name);

                }

                

                // add new option

                echo '<li onclick="set_item_shipment_to_sub(\''.$rs->warehouse_id.'\',\''.$rs->warehouse_name.'\')">'.$warehouse_name.'</li>';

            }

        }

    }

    public function getcustomer(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $customer_model = $this->model->get('customerModel');

            

            if ($_POST['keyword'] == "*") {



                $list = $customer_model->getAllCustomer();

            }

            else{

                $data = array(

                'where'=>'( customer_name LIKE "%'.$_POST['keyword'].'%" )',

                );

                $list = $customer_model->getAllCustomer($data);

            }

            

            foreach ($list as $rs) {

                // put in bold the written text

                $customer_name = $rs->customer_name;

                if ($_POST['keyword'] != "*") {

                    $customer_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->customer_name);

                }

                

                // add new option

                echo '<li onclick="set_item_customer(\''.$rs->customer_id.'\',\''.$rs->customer_name.'\')">'.$customer_name.'</li>';

            }

        }

    }

    public function getcustomersub(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $customer_model = $this->model->get('customerModel');

            

            if ($_POST['keyword'] == "*") {



                $list = $customer_model->getAllCustomer();

            }

            else{

                $data = array(

                'where'=>'( customer_name LIKE "%'.$_POST['keyword'].'%" )',

                );

                $list = $customer_model->getAllCustomer($data);

            }

            

            foreach ($list as $rs) {

                // put in bold the written text

                $customer_name = $rs->customer_name;

                if ($_POST['keyword'] != "*") {

                    $customer_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->customer_name);

                }

                

                // add new option

                echo '<li onclick="set_item_customer_sub(\''.$rs->customer_id.'\',\''.$rs->customer_name.'\')">'.$customer_name.'</li>';

            }

        }

    }

    public function getvehicle(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $vehicle_model = $this->model->get('vehicleModel');

            

            if ($_POST['keyword'] == "*") {



                $list = $vehicle_model->getAllVehicle();

            }

            else{

                $data = array(

                'where'=>'( vehicle_number LIKE "%'.$_POST['keyword'].'%" )',

                );

                $list = $vehicle_model->getAllVehicle($data);

            }

            

            foreach ($list as $rs) {

                // put in bold the written text

                $vehicle_number = $rs->vehicle_number;

                if ($_POST['keyword'] != "*") {

                    $vehicle_number = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->vehicle_number);

                }

                

                // add new option

                echo '<li onclick="set_item_vehicle(\''.$rs->vehicle_id.'\',\''.$rs->vehicle_number.'\')">'.$vehicle_number.'</li>';

            }

        }

    }

    public function getvehiclesub(){

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $vehicle_model = $this->model->get('vehicleModel');

            

            if ($_POST['keyword'] == "*") {



                $list = $vehicle_model->getAllVehicle();

            }

            else{

                $data = array(

                'where'=>'( vehicle_number LIKE "%'.$_POST['keyword'].'%" )',

                );

                $list = $vehicle_model->getAllVehicle($data);

            }

            

            foreach ($list as $rs) {

                // put in bold the written text

                $vehicle_number = $rs->vehicle_number;

                if ($_POST['keyword'] != "*") {

                    $vehicle_number = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->vehicle_number);

                }

                

                // add new option

                echo '<li onclick="set_item_vehicle_sub(\''.$rs->vehicle_id.'\',\''.$rs->vehicle_number.'\')">'.$vehicle_number.'</li>';

            }

        }

    }

    



    public function delete(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $shipment = $this->model->get('shipmentModel');

            $shipment_temp = $this->model->get('shipmenttempModel');

            if (isset($_POST['xoa'])) {

                $data = explode(',', $_POST['xoa']);

                foreach ($data as $data) {

                    $shipment_data = $shipment->getShipment($data);

                    if ($shipment_data->shipment_temp > 0) {

                        $shipment_temp_data = $shipment_temp->getShipment($shipment_data->shipment_temp);

                        $data_shipment = array(

                            'shipment_temp_status' => 0,

                            'shipment_ton_use' => $shipment_temp_data->shipment_ton_use-$shipment_data->shipment_ton,

                        );

                        $shipment_temp->updateShipment($data_shipment,array('shipment_temp_id'=>$shipment_data->shipment_temp));



                    }



                    $shipment->deleteShipment($data);



                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|shipment|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                }

                return true;

            }

            else{



                $shipment_data = $shipment->getShipment($_POST['data']);

                if ($shipment_data->shipment_temp > 0) {

                    $shipment_temp_data = $shipment_temp->getShipment($shipment_data->shipment_temp);

                    

                    $data_shipment = array(

                        'shipment_temp_status' => 0,

                        'shipment_ton_use' => $shipment_temp_data->shipment_ton_use-$shipment_data->shipment_ton,

                    );

                    $shipment_temp->updateShipment($data_shipment,array('shipment_temp_id'=>$shipment_data->shipment_temp));



                }



                date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|shipment|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);



                return $shipment->deleteShipment($_POST['data']);

            }

            

        }

    }



    

    



    public function view() {

        $this->view->disableLayout();



        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        

        $this->view->data['lib'] = $this->lib;



        $id = $this->registry->router->param_id;

        $round = $this->registry->router->page;



        $thang = $this->registry->router->order_by;

        $nam = $this->registry->router->order;



        $this->view->data['id'] = $id;

        $this->view->data['round'] = $round;

        $this->view->data['thang'] = $thang;

        $this->view->data['nam'] = $nam;



        $batdau = strtotime('30-'.($thang-1).'-'.$nam);

        $ketthuc = strtotime('29-'.$thang.'-'.$nam);





        $driver_model = $this->model->get('driverModel');

        



        





        $shipment_model = $this->model->get('shipmentModel');



        $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');

        $data = array(

            'where' => 'vehicle = '.$id.' AND shipment_round <= '.$round.' AND shipment_date >= '.$batdau.' AND shipment_date <= '.$ketthuc,

            'order_by'=> 'shipment_date ASC, shipment_round ASC',

            );

        

        $this->view->data['shipments'] = $shipment_model->getAllShipment($data,$join);



        $warehouse_model = $this->model->get('warehouseModel');

        



        $road_model = $this->model->get('roadModel');



        $warehouse_data = array();

        $road_data = array();



        $v = array();



        $driver_data = array();



        foreach ($this->view->data['shipments'] as $shipment) {



            $d_data = array(

                'where'=> ' start_work <= '.$shipment->shipment_date.' AND end_work > '.$shipment->shipment_date.' AND vehicle = '.$shipment->vehicle,

            );

            $drivers = $driver_model->getAllDriver($d_data);

            

            foreach ($drivers as $driver) {

                $driver_data[$shipment->shipment_id]['driver_name'] = $driver->driver_name;

                $driver_data[$shipment->shipment_id]['driver_phone'] = $driver->driver_phone;

            }



            $month = intval(date('m',$shipment->shipment_date));

            $year = date('Y',$shipment->shipment_date);

            if(date('d',$shipment->shipment_date)>29){

                $month = intval(date('m',$shipment->shipment_date)+1);
                if ($month == 13) {
                    $month = 1;
                    $year = $year+1;
                }

            }



           $v[$shipment->vehicle.$shipment->shipment_round.$month.$year] = isset($v[$shipment->vehicle.$shipment->shipment_round.$month.$year])?($v[$shipment->vehicle.$shipment->shipment_round.$month.$year] + 1) : (0+1) ;



            

            $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$shipment->shipment_from.' AND road_to = '.$shipment->shipment_to.' AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));

            

           $check_sub = 1;

           if ($shipment->shipment_sub==1) {

               $check_sub = 0;

           }



            $chek_rong = 0;

            

            foreach ($roads as $road) {

                $road_data['road_id'][$shipment->shipment_id] = $road->road_id;

                $road_data['way'][$shipment->shipment_id] = $road->way;

                $road_data['road_km'][$shipment->shipment_id] = $road->road_km;

                $road_data['road_oil'][$shipment->shipment_id] = ($road->road_oil)*$check_sub;

                $road_data['bridge_cost'][$shipment->shipment_id] = (round($road->bridge_cost*1.1))*$check_sub;

                $road_data['police_cost'][$shipment->shipment_id] = ($road->police_cost)*$check_sub;

                $road_data['tire_cost'][$shipment->shipment_id] = ($road->tire_cost)*$check_sub;

                $road_data['oil_cost'][$shipment->shipment_id] = ($road->road_oil*round($shipment->oil_cost*1.1))*$check_sub;



                $road_data['road_time'][$shipment->shipment_id] = ($road->road_time)*$check_sub;



                $chek_rong = ($road->way == 0)?1:0;



            }



            $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$shipment->shipment_from.' OR warehouse_code = '.$shipment->shipment_to.') AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));

        



            $boiduong_cont = 0;

            $boiduong_tan = 0;



            

            foreach ($warehouse as $warehouse) {

                

                    $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;

                    $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name;



                    $tan = explode(".",$shipment->shipment_ton);

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

                    if ($shipment->shipment_ton > 0) {

                        $boiduong_cont += $warehouse->warehouse_add;

                    }

                }

                

            }

            $warehouse_data['boiduong_cn'][$shipment->shipment_id] = ($boiduong_cont+$boiduong_tan)*$check_sub;

            





        }



        $this->view->data['driver_data'] = $driver_data;



        $this->view->data['warehouse'] = $warehouse_data;



        $this->view->data['road'] = $road_data;

        $this->view->data['arr'] = $v;



        $this->view->show('shipment/view');

    }



    public function import(){

        $this->view->disableLayout();

        header('Content-Type: text/html; charset=utf-8');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_FILES['import']['name'] != null) {



            require("lib/Classes/PHPExcel/IOFactory.php");

            require("lib/Classes/PHPExcel.php");



            $warehouse = $this->model->get('warehouseModel');

            $shipment = $this->model->get('shipmentModel');

            $road = $this->model->get('roadModel');

            $customer = $this->model->get('customerModel');

            $vehicle = $this->model->get('vehicleModel');



            $objPHPExcel = new PHPExcel();

            // Set properties

            if (pathinfo($_FILES['import']['name'], PATHINFO_EXTENSION) == "xls") {

                $objReader = PHPExcel_IOFactory::createReader('Excel5');

            }

            else if (pathinfo($_FILES['import']['name'], PATHINFO_EXTENSION) == "xlsx") {

                $objReader = PHPExcel_IOFactory::createReader('Excel2007');

            }

            

            $objReader->setReadDataOnly(false);



            $objPHPExcel = $objReader->load($_FILES['import']['tmp_name']);

            $objWorksheet = $objPHPExcel->getActiveSheet();



            



            $highestRow = $objWorksheet->getHighestRow(); // e.g. 10

            $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'



            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5



            //var_dump($objWorksheet->getMergeCells());die();

            

             



                for ($row = 3; $row <= $highestRow; ++ $row) {

                    $val = array();

                    for ($col = 0; $col < $highestColumnIndex; ++ $col) {

                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);

                        // Check if cell is merged

                        foreach ($objWorksheet->getMergeCells() as $cells) {

                            if ($cell->isInRange($cells)) {

                                $currMergedCellsArray = PHPExcel_Cell::splitRange($cells);

                                $cell = $objWorksheet->getCell($currMergedCellsArray[0][0]);

                                break;

                                

                            }

                        }

                        $val[] = $cell->getValue();



                        //$val[] = $cell->getCalculatedValue();

                        //here's my prob..

                        //echo $val;

                    }

                    if ($val[0] != null && $val[1] != null && $val[4] != null && $val[5] != null) {



                            if(!$warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[4])))) {

                                $warehouse_data = array(

                                'warehouse_name' => trim($val[4]),

                                'warehouse_cont' => trim($val[16]),

                                'warehouse_ton' => (trim($val[17])>0)?(trim($val[17])/trim($val[11])):0,

                                );

                                $warehouse->createWarehouse($warehouse_data);



                                $warehouse_from = $warehouse->getLastWarehouse()->warehouse_id;

                            }

                            else if($warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[4])))){

                                $warehouse_from = $warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[4])))->warehouse_id;

                                $warehouse_cont = $warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[4])))->warehouse_cont;

                                $warehouse_ton = $warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[4])))->warehouse_ton;

                                $warehouse_data = array(

                                'warehouse_cont' => $warehouse_cont==0?trim($val[16]):$warehouse_cont,

                                'warehouse_ton' => $warehouse_ton==0?((trim($val[17])>0)?(trim($val[17])/trim($val[11])):0):$warehouse_ton,

                                );



                                $warehouse->updateWarehouse($warehouse_data,array('warehouse_id'=>$warehouse_from));

                                

                            }



                            if(!$warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[5])))) {

                                $warehouse_data = array(

                                'warehouse_name' => trim($val[5]),

                                'warehouse_cont' => trim($val[18]),

                                'warehouse_ton' => (trim($val[19])>0)?(trim($val[19])/trim($val[11])):0,

                                );

                                $warehouse->createWarehouse($warehouse_data);



                                $warehouse_to = $warehouse->getLastWarehouse()->warehouse_id;

                            }

                            else if($warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[5])))){

                                $warehouse_to = $warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[5])))->warehouse_id;

                                $warehouse_cont = $warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[5])))->warehouse_cont;

                                $warehouse_ton = $warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[5])))->warehouse_ton;

                                $warehouse_data = array(

                                'warehouse_cont' => $warehouse_cont==0?trim($val[18]):$warehouse_cont,

                                'warehouse_ton' => $warehouse_ton==0?((trim($val[19])>0)?(trim($val[19])/trim($val[11])):0):$warehouse_ton,

                                );



                                $warehouse->updateWarehouse($warehouse_data,array('warehouse_id'=>$warehouse_to));

                            }



                            if(!$vehicle->getVehicleByWhere(array('vehicle_number'=>trim($val[1])))) {

                                $vehicle_data = array(

                                'vehicle_number' => trim($val[1]),

                                'driver_name' => trim($val[2]),

                                );

                                $vehicle->createVehicle($vehicle_data);



                                $vehicle_id = $vehicle->getLastVehicle()->vehicle_id;

                            }

                            else if($vehicle->getVehicleByWhere(array('vehicle_number'=>trim($val[1])))){

                                $vehicle_id = $vehicle->getVehicleByWhere(array('vehicle_number'=>trim($val[1])))->vehicle_id;

                                



                            }



                            if(!$customer->getCustomerByWhere(array('customer_name'=>trim($val[7])))) {

                                $customer_data = array(

                                'customer_name' => trim($val[7]),

                                'customer_contact' => trim($val[8]),

                                'customer_phone' => trim($val[9]),

                                );

                                $customer->createCustomer($customer_data);



                                $customer_id = $customer->getLastCustomer()->customer_id;

                            }

                            else if($customer->getCustomerByWhere(array('customer_name'=>trim($val[7])))){

                                $customer_id = $customer->getCustomerByWhere(array('customer_name'=>trim($val[7])))->customer_id;

                                



                            }



                            if( trim($val[6]) == "Rỗng"){

                                $way = 0;

                            }

                            else if( trim($val[6]) == "Lên"){

                                $way = 1;

                            }

                            else if( trim($val[6]) == "Xuống"){

                                $way = 2;

                            }

                            else if( trim($val[6]) == "Bằng"){

                                $way = 3;

                            }

                            else if( trim($val[6]) == "ĐN-QN"){

                                $way = 4;

                            }



                            if(!$road->getRoadByWhere(array('road_from'=>trim($warehouse_from),'road_to'=>trim($warehouse_to)))) {

                                

                                

                                

                                $road_data = array(

                                'road_from' => $warehouse_from,

                                'road_to' => $warehouse_to,

                                'way' => $way,

                                'road_km' => trim($val[12]),

                                'road_oil' => trim($val[13]),

                                'bridge_cost' => trim($val[20]),

                                'police_cost' => trim($val[21]),

                                'tire_cost' => trim($val[22]),

                                'road_time' => trim($val[15]),

                                );

                                $road->createRoad($road_data);



                                $road_id = $road->getLastRoad()->road_id;

                            }

                            else if($road->getRoadByWhere(array('road_from'=>trim($warehouse_from),'road_to'=>trim($warehouse_to)))){

                                $road_id = $road->getRoadByWhere(array('road_from'=>trim($warehouse_from),'road_to'=>trim($warehouse_to)))->road_id;

                                $road_data = array(

                                'way' => $way,

                                'road_km' => trim($val[12]),

                                'road_oil' => trim($val[13]),

                                'bridge_cost' => trim($val[20]),

                                'police_cost' => trim($val[21]),

                                'tire_cost' => trim($val[22]),

                                'road_time' => trim($val[15]),

                                );

                                $road->updateRoad($road_data,array('road_id'=>$road_id));

                            }





                            $warehouses = $warehouse->getAllWarehouse(array('where'=>'warehouse_id = '.$warehouse_from.' OR warehouse_id = '.$warehouse_to));

                            $roads = $road->getAllRoad(array('where'=>'road_from = '.$warehouse_from.' AND road_to = '.$warehouse_to));



                            $boiduong_cont = 0;

                            $boiduong_tan = 0;

                            $doanhthu = 0;

                            $chiphi = 0;

                            $loinhuan = 0;



                            $giadau = 19; //gia dau hien tai



                            if ($warehouses) {

                                foreach ($warehouses as $warehouse_datas) {

                                    if ($warehouse_datas->warehouse_cont != 0) {

                                        $boiduong_cont += $warehouse_datas->warehouse_cont;

                                    }

                                    if ($warehouse_datas->warehouse_ton != 0){

                                        $boiduong_tan += trim($val[11]) * $warehouse_datas->warehouse_ton;

                                    }

                                }

                            }

                            $boiduong = 0;

                            if ($roads) {

                                foreach ($roads as $road_datas) {

                                    $boiduong += ($road_datas->way == 0)?0:($boiduong_tan+$boiduong_cont);

                                    $chiphi += ($road_datas->road_time*3000000)+$boiduong+$road_datas->police_cost+$road_datas->bridge_cost+$road_datas->tire_cost+($road_datas->road_oil*$giadau);

                                    

                                }

                            }



                            //$chiphi = $chiphi+$data['cost_add'];



                            $doanhthu = trim($val[10])*trim($val[11]);

                            $loinhuan = $doanhthu - $chiphi;



                            

/*

                            $ngay_ship = substr(trim($val[3]), 3,2);

                            $thang_ship = substr(trim($val[3]), 0,2);

                            $nam_ship = substr(trim($val[3]), 6,4);



                            $ngaythang = $ngay_ship.'-'.$thang_ship.'-'.$nam_ship;*/



                            $ngaythang = PHPExcel_Shared_Date::ExcelToPHP(trim($val[3]));                                      

                            $ngaythang = $ngaythang-3600;



                            if ($shipment->getShipmentByWhere(array('shipment_from'=>$warehouse_from,'shipment_to'=>$warehouse_to,'customer'=>$customer_id,'vehicle'=>$vehicle_id,'shipment_date'=>$ngaythang,'shipment_round'=>trim($val[0])))) {

                                $shipment_id = $shipment->getShipmentByWhere(array('shipment_from'=>$warehouse_from,'shipment_to'=>$warehouse_to,'customer'=>$customer_id,'vehicle'=>$vehicle_id,'shipment_date'=>$ngaythang,'shipment_round'=>trim($val[0])))->shipment_id;

                                

                                $data_shipment = array(

                        

                                'shipment_from' => $warehouse_from,

                                'shipment_to' => $warehouse_to,

                                'vehicle' => $vehicle_id,

                                'customer' => $customer_id,

                                'shipment_ton' => trim($val[11]),

                                'shipment_charge' => trim($val[10]),

                                'oil_add' => trim($val[14]),

                                'cost_add' => trim($val[23]),

                                'oil_cost' => round(19000/1.1),

                                'shipment_date' => $ngaythang,

                                'shipment_round' => trim($val[0]),

                                'shipment_create_user' => $_SESSION['userid_logined'],

                                'approve' => 1,

                                );

                                $data_shipment['shipment_revenue'] = $doanhthu;

                                $data_shipment['shipment_cost'] = $chiphi;

                                $data_shipment['shipment_profit'] = $loinhuan;



                                $shipment->updateShipment($data_shipment,array('shipment_id'=>$shipment_id));



                            }

                            else if (!$shipment->getShipmentByWhere(array('shipment_from'=>$warehouse_from,'shipment_to'=>$warehouse_to,'customer'=>$customer_id,'vehicle'=>$vehicle_id,'shipment_date'=>$ngaythang))) {

                                

                                

                                $data_shipment = array(

                        

                                'shipment_from' => $warehouse_from,

                                'shipment_to' => $warehouse_to,

                                'vehicle' => $vehicle_id,

                                'customer' => $customer_id,

                                'shipment_ton' => trim($val[11]),

                                'shipment_charge' => trim($val[10]),

                                'oil_add' => trim($val[14]),

                                'cost_add' => trim($val[23]),

                                'oil_cost' => round(19000/1.1),

                                'shipment_date' => $ngaythang,

                                'shipment_round' => trim($val[0]),

                                'shipment_create_user' => $_SESSION['userid_logined'],

                                'approve' => 1,

                                );



                                $data_shipment['shipment_revenue'] = $doanhthu;

                                $data_shipment['shipment_cost'] = $chiphi;

                                $data_shipment['shipment_profit'] = $loinhuan;





                                $shipment->createShipment($data_shipment);



                            }

                        

                    }

                    

                    //var_dump($this->getNameDistrict($this->lib->stripUnicode($val[1])));

                    // insert





                }

                //return $this->view->redirect('transport');

            

            return $this->view->redirect('shipment');

        }

        $this->view->show('shipment/import');



    }



    public function report() {

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        $this->view->data['lib'] = $this->lib;

        $this->view->data['title'] = 'Kết quả hoạt động của đội xe';



        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;

            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;

        }

        else{

            $batdau = '01-'.date('m-Y');

            $ketthuc = date('d-m-Y'); //cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).'-'.date('m-Y');

        }



        $data = null;

        /*if ($_SESSION['role_logined'] == 3) {

            $data = array('where'=>'shipment_create_user = '.$_SESSION['userid_logined']);

        }*/

        $vehicle_model = $this->model->get('vehicleModel');

        $join_v = array('table'=>'shipment','where'=>'vehicle.vehicle_id = shipment.vehicle GROUP BY vehicle_id');

        $vehicles = $vehicle_model->getAllVehicle($data,$join_v);



        $shipment_model = $this->model->get('shipmentModel');

        $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');

        $data = array(

            'where'=>'shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc),

        );



        if ($_SESSION['role_logined'] == 3) {

            if( (strtotime($batdau) < strtotime('04-06-2015')) && (strtotime($ketthuc) > strtotime('04-06-2015')) )

                $data['where'] = '( (shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime('04-06-2015').') OR (shipment_date > '.strtotime('04-06-2015').' AND shipment_date <= '.strtotime($ketthuc).' AND shipment_create_user = '.$_SESSION['userid_logined'].') )';

            else if( strtotime($batdau) > strtotime('04-06-2015') )

                $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];

        }



        /*if ($_SESSION['role_logined'] == 3) {

            $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];

        }*/



        $shipments = $shipment_model->getAllShipment($data,$join);



        $doanhthu = array();

        $chiphiphatsinh = array();



        $warehouse_model = $this->model->get('warehouseModel');

        



        $road_model = $this->model->get('roadModel');



        $warehouse_data = array();

        $road_data = array();





        foreach ($shipments as $shipment) {

            $doanhthu[$shipment->vehicle] = isset($doanhthu[$shipment->vehicle])?($doanhthu[$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other) : (0+$shipment->shipment_revenue+$shipment->revenue_other);

            $chiphiphatsinh[$shipment->vehicle] = isset($chiphiphatsinh[$shipment->vehicle])?($chiphiphatsinh[$shipment->vehicle]+(($shipment->approve==1)?$shipment->cost_add:0)) : (0+(($shipment->approve==1)?$shipment->cost_add:0));



            $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$shipment->shipment_from.' AND road_to = '.$shipment->shipment_to.' AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));

            

           

            $check_rong = 0;

            

            foreach ($roads as $road) {

                $road_data['bridge_cost'][$shipment->vehicle] = isset($road_data['bridge_cost'][$shipment->vehicle])?($road_data['bridge_cost'][$shipment->vehicle]+$road->bridge_cost):0+$road->bridge_cost;

                $road_data['police_cost'][$shipment->vehicle] = isset($road_data['police_cost'][$shipment->vehicle])?($road_data['police_cost'][$shipment->vehicle]+$road->police_cost):0+$road->police_cost;

                $road_data['oil_cost'][$shipment->vehicle] = isset($road_data['oil_cost'][$shipment->vehicle])?($road_data['oil_cost'][$shipment->vehicle]+($road->road_oil*$shipment->oil_cost)):0+($road->road_oil*$shipment->oil_cost);

                $road_data['road_time'][$shipment->vehicle] = isset($road_data['road_time'][$shipment->vehicle])?($road_data['road_time'][$shipment->vehicle]+$road->road_time):0+$road->road_time;

                $road_data['tire_cost'][$shipment->vehicle] = isset($road_data['tire_cost'][$shipment->vehicle])?($road_data['tire_cost'][$shipment->vehicle]+$road->tire_cost):0+$road->tire_cost;



                $chek_rong = ($road->way == 0)?1:0;

            }





            $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$shipment->shipment_from.' OR warehouse_code = '.$shipment->shipment_to.') AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));

        



            $boiduong_cont = 0;

            $boiduong_tan = 0;



            

            foreach ($warehouse as $warehouse) {

                $tan = explode(".",$shipment->shipment_ton);

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

                    if ($shipment->shipment_ton > 0) {

                        $boiduong_cont += $warehouse->warehouse_add;

                    }

                }

            }

            $warehouse_data['boiduong_cn'][$shipment->vehicle] = isset($warehouse_data['boiduong_cn'][$shipment->vehicle])?($warehouse_data['boiduong_cn'][$shipment->vehicle]+$boiduong_cont+$boiduong_tan):0+$boiduong_cont+$boiduong_tan;

            

            if ($shipment->sub_driver == 1) {

                $warehouse_data['sub_driver']['boiduong_cn'][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$shipment->vehicle]+$boiduong_cont+$boiduong_tan):0+$boiduong_cont+$boiduong_tan;    

            }



            if ($shipment->sub_driver2 == 1) {

                $warehouse_data['sub_driver']['boiduong_cn'][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$shipment->vehicle]+$boiduong_cont+$boiduong_tan):0+$boiduong_cont+$boiduong_tan;    

            }



           

            

        }



        $this->view->data['vehicles'] = $vehicles;

        $this->view->data['warehouse'] = $warehouse_data;

        $this->view->data['road'] = $road_data;



        $this->view->data['doanhthu'] = $doanhthu;

        $this->view->data['chiphiphatsinh'] = $chiphiphatsinh;



        $this->view->data['batdau'] = $batdau;

        $this->view->data['ketthuc'] = $ketthuc;

        $this->view->show('shipment/report');

    }



    function export(){

        $this->view->disableLayout();

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }



            $batdau = ($this->registry->router->param_id != "") ? $this->registry->router->param_id : ('30-'.date('m-Y', strtotime("last month")));

            $ketthuc = ($this->registry->router->page != "") ? $this->registry->router->page : (date('d-m-Y', time()+86400));

            $xe = ($this->registry->router->order_by != 0) ? $this->registry->router->order_by : "";

            $vong = ($this->registry->router->order != 0) ? $this->registry->router->order : "";

        

        

            $warehouse_model = $this->model->get('warehouseModel');

            $shipment_model = $this->model->get('shipmentModel');



            $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');

            $data = array(

                'where' => 'shipment_date >= '.$batdau.' AND shipment_date <= '.$ketthuc,

                );



            if ($_SESSION['role_logined'] == 3) {

            if( ($batdau < strtotime('04-06-2015')) && ($ketthuc > strtotime('04-06-2015')) )

                $data['where'] = '( (shipment_date >= '.$batdau.' AND shipment_date <= '.strtotime('04-06-2015').') OR (shipment_date > '.strtotime('04-06-2015').' AND shipment_date <= '.$ketthuc.' AND shipment_create_user = '.$_SESSION['userid_logined'].') )';

            else if( $batdau > strtotime('04-06-2015') )

                $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];

        }





            if($xe != ""){

                $data['where'] = $data['where'].' AND vehicle = '.$xe;

            }

            if($vong != ""){

                $data['where'] = $data['where'].' AND shipment_round = '.$vong;

            }



            /*if ($_SESSION['role_logined'] == 3) {

                $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];

                



            }*/

            $shipments = $shipment_model->getAllShipment($data,$join);









            require("lib/Classes/PHPExcel/IOFactory.php");

            require("lib/Classes/PHPExcel.php");



            $objPHPExcel = new PHPExcel();



            



            $index_worksheet = 0; //(worksheet mặc định là 0, nếu tạo nhiều worksheet $index_worksheet += 1)

            $objPHPExcel->setActiveSheetIndex($index_worksheet)

               ->setCellValue('A1', 'STT')

               ->setCellValue('B1', 'Xe')

               ->setCellValue('C1', 'Ngày')

               ->setCellValue('D1', 'Kho đi')

               ->setCellValue('E1', 'Kho đến')

               ->setCellValue('F1', 'Khách hàng')

               ->setCellValue('G1', 'Cước')

               ->setCellValue('H1', 'Trọng tải')

               ->setCellValue('I1', 'Doanh thu');

               



            



            

            

            



            if ($shipments) {



                $hang = 2;

                $i=1;



                $kho_data = array();

                foreach ($shipments as $row) {

                    if ($row->shipment_charge != 0 && $row->shipment_ton != 0) {

                    

                    $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'warehouse_id = '.$row->shipment_from.' OR warehouse_id = '.$row->shipment_to));





                    foreach ($warehouse as $warehouse) {

                        

                            $warehouse_data['warehouse_name'][$warehouse->warehouse_id] = $warehouse->warehouse_name;

                     

                        

                    }



                    //$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$hang)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );

                     $objPHPExcel->setActiveSheetIndex(0)

                        ->setCellValue('A' . $hang, $i++)

                        ->setCellValueExplicit('B' . $hang, $row->vehicle_number)

                        ->setCellValue('C' . $hang, $this->lib->hien_thi_ngay_thang($row->shipment_date))

                        ->setCellValue('D' . $hang, $warehouse_data['warehouse_name'][$row->shipment_from])

                        ->setCellValue('E' . $hang, $warehouse_data['warehouse_name'][$row->shipment_to])

                        ->setCellValue('F' . $hang, $row->customer_name)

                        ->setCellValue('G' . $hang, $row->shipment_charge)

                        ->setCellValue('H' . $hang, $row->shipment_ton)

                        ->setCellValue('I' . $hang, $row->shipment_revenue+$row->revenue_other);

                     $hang++;





                  }

              }



          }



            $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();



            $highestRow ++;





            $objPHPExcel->getActiveSheet()->getStyle('G2:I'.$highestRow)->getNumberFormat()->setFormatCode("#,##0_);[Black](#,##0)");

            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);

            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(16);

            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(14);

            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);

            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);



            // Set properties

            $objPHPExcel->getProperties()->setCreator("TCMT")

                            ->setLastModifiedBy($_SESSION['user_logined'])

                            ->setTitle("Shipment Report")

                            ->setSubject("Shipment Report")

                            ->setDescription("Shipment Report.")

                            ->setKeywords("Shipment Report")

                            ->setCategory("Shipment Report");

            $objPHPExcel->getActiveSheet()->setTitle("Thong tin lo hang");



            $objPHPExcel->getActiveSheet()->freezePane('A1');

            $objPHPExcel->setActiveSheetIndex(0);







            



            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');



            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

            header("Content-Disposition: attachment; filename= BẢNG LÔ HÀNG.xlsx");

            header("Cache-Control: max-age=0");

            ob_clean();

            $objWriter->save("php://output");

        

    }



    function viewexport(){

        $this->view->disableLayout();

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }



            $id = $this->registry->router->param_id;

        $round = $this->registry->router->page;



        $thang = $this->registry->router->order_by;

        $nam = $this->registry->router->order;



        $batdau = strtotime('30-'.($thang-1).'-'.$nam);

        $ketthuc = strtotime('29-'.$thang.'-'.$nam);



        $shipment_model = $this->model->get('shipmentModel');



        $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');

        $data = array(

            'where' => 'vehicle = '.$id.' AND shipment_round = '.$round.' AND shipment_date >= '.$batdau.' AND shipment_date <= '.$ketthuc,

            );

        

        $shipments = $shipment_model->getAllShipment($data,$join);



        $warehouse_model = $this->model->get('warehouseModel');

        



        $road_model = $this->model->get('roadModel');



        $warehouse_data = array();

        $road_data = array();











            require("lib/Classes/PHPExcel/IOFactory.php");

            require("lib/Classes/PHPExcel.php");



            $objPHPExcel = new PHPExcel();



            



            $index_worksheet = 0; //(worksheet mặc định là 0, nếu tạo nhiều worksheet $index_worksheet += 1)

            $objPHPExcel->setActiveSheetIndex($index_worksheet)

               ->setCellValue('A1', 'STT')

               ->setCellValue('B1', 'Xe')

               ->setCellValue('C1', 'Ngày')

               ->setCellValue('D1', 'Kho đi')

               ->setCellValue('E1', 'Kho đến')

               ->setCellValue('F1', 'Khách hàng')

               ->setCellValue('G1', 'Chiều')

               ->setCellValue('H1', 'KM')

               ->setCellValue('I1', 'Định mức dầu')

               ->setCellValue('J1', 'Ứng dầu')

               ->setCellValue('K1', 'Dầu còn lại');

               



            



            

            

            



            if ($shipments) {



                $hang = 2;

                $i=1;



                foreach ($shipments as $shipment) {

            

                    $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$shipment->shipment_from.' AND road_to = '.$shipment->shipment_to.' AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));

                    

                   

                    $chek_rong = 0;

                    

                    foreach ($roads as $road) {

                        $road_data['road_id'][$shipment->shipment_id] = $road->road_id;

                        $road_data['way'][$shipment->shipment_id] = $road->way;

                        $road_data['road_km'][$shipment->shipment_id] = $road->road_km;

                        $road_data['road_oil'][$shipment->shipment_id] = $road->road_oil;

                        $road_data['bridge_cost'][$shipment->shipment_id] = round($road->bridge_cost*1.1);

                        $road_data['police_cost'][$shipment->shipment_id] = $road->police_cost;

                        $road_data['tire_cost'][$shipment->shipment_id] = $road->tire_cost;

                        $road_data['oil_cost'][$shipment->shipment_id] = $road->road_oil*round($shipment->oil_cost*1.1);

                        $road_data['road_time'][$shipment->shipment_id] = $road->road_time;



                        $chek_rong = ($road->way == 0)?1:0;



                    }



                    $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$shipment->shipment_from.' OR warehouse_code = '.$shipment->shipment_to.') AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));

                



                    $boiduong_cont = 0;

                    $boiduong_tan = 0;



                    

                    foreach ($warehouse as $warehouse) {

                        

                            $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;

                            $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name;



                            $tan = explode(".",$shipment->shipment_ton);

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

                            if ($shipment->shipment_ton > 0) {

                                $boiduong_cont += $warehouse->warehouse_add;

                            }

                        }

                        

                    }

                    $warehouse_data['boiduong_cn'][$shipment->shipment_id] = $boiduong_cont+$boiduong_tan;

            





                $chieu  = $road_data['way'][$shipment->shipment_id]==1?'Lên':($road_data['way'][$shipment->shipment_id]==2?'Xuống':($road_data['way'][$shipment->shipment_id]==3?'Bằng':($road_data['way'][$shipment->shipment_id]==4?'ĐN-QN':'Rỗng')));



                    //$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$hang)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );

                     $objPHPExcel->setActiveSheetIndex(0)

                        ->setCellValue('A' . $hang, $i++)

                        ->setCellValueExplicit('B' . $hang, $shipment->vehicle_number)

                        ->setCellValue('C' . $hang, $this->lib->hien_thi_ngay_thang($shipment->shipment_date))

                        ->setCellValue('D' . $hang, $warehouse_data['warehouse_name'][$shipment->shipment_from])

                        ->setCellValue('E' . $hang, $warehouse_data['warehouse_name'][$shipment->shipment_to])

                        ->setCellValue('F' . $hang, $shipment->customer_name)

                        ->setCellValue('G' . $hang, $chieu)

                        ->setCellValue('H' . $hang, $road_data['road_km'][$shipment->shipment_id])

                        ->setCellValue('I' . $hang, $road_data['road_oil'][$shipment->shipment_id])

                        ->setCellValue('J' . $hang, $shipment->oil_add)

                        ->setCellValue('K' . $hang, '=I'.$hang.'-J'.$hang);

                     $hang++;





                  }

              }



          



            $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();



            $highestRow ++;





            $objPHPExcel->getActiveSheet()->getStyle('H2:K'.$highestRow)->getNumberFormat()->setFormatCode("#,##0_);[Black](#,##0)");

            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);

            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(16);

            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(14);

            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);

            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);



            // Set properties

            $objPHPExcel->getProperties()->setCreator("TCMT")

                            ->setLastModifiedBy($_SESSION['user_logined'])

                            ->setTitle("Shipment Report")

                            ->setSubject("Shipment Report")

                            ->setDescription("Shipment Report.")

                            ->setKeywords("Shipment Report")

                            ->setCategory("Shipment Report");

            $objPHPExcel->getActiveSheet()->setTitle("Thong tin lo hang");



            $objPHPExcel->getActiveSheet()->freezePane('A1');

            $objPHPExcel->setActiveSheetIndex(0);







            



            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');



            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

            header("Content-Disposition: attachment; filename= BẢNG LÔ HÀNG.xlsx");

            header("Cache-Control: max-age=0");

            ob_clean();

            $objWriter->save("php://output");

        

    }



    public function craw(){

        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }



        require('lib/simple_html_dom.php');

        //get link;

        $url ="http://www.petrolimex.com.vn/index.html"; 



        $content = $this->get_content_by_url($url); 

    

        $content = $this->get_content_by_tag($content,"<div id=\"vie_p3_PortletContent\" class=\"blueseaContainerContent\" style=\"height:130px;\">",$url); 



        $arr = array();



        $html = str_get_html($content);

        if (!$html) {

            return 0;

        }

        

        foreach($html->find('div.c') as $element)

        {

            $arr[] = $element->plaintext;

        }



        return str_replace('.', '', $arr['6']);

        

    }

    /// functions lay tin

    public static function get_content_by_url($url){

        $content = file_get_contents($url);

        do{

            $content = str_replace("  "," ",$content);

        }while(strpos($content,"  ",0)!==false);

        return $content;

    }

    

   

    public static function get_content_by_tag($content, $tag_and_more,$include_tag = true){

        $p = stripos($content,$tag_and_more,0);

        

        if($p===false) return "";

        $content=substr($content,$p);

        $p = stripos($content," ",0);

        if(abs($p)==0) return "";

        $open_tag = substr($content,0,$p);

        $close_tag = substr($open_tag,0,1)."/".substr($open_tag,1).">";

        

        $count_inner_tag = 0;

        $p_open_inner_tag = 1; 

        $p_close_inner_tag = 0;

        $count=1;

        do{

            $p_open_inner_tag = stripos($content,$open_tag,$p_open_inner_tag);

            $p_close_inner_tag = stripos($content,$close_tag,$p_close_inner_tag);

            $count++;

            if($p_close_inner_tag!==false) $p = $p_close_inner_tag;

            if($p_open_inner_tag!==false){

                if(abs($p_open_inner_tag)<abs($p_close_inner_tag)){

                    $count_inner_tag++;

                    $p_open_inner_tag++;

                }else{

                    $count_inner_tag--;

                    $p_close_inner_tag++;

                }

            }else{

                $count_inner_tag--;

                if($p_close_inner_tag>0) $p_close_inner_tag++;

            }

        }while($count_inner_tag>0);

        if($include_tag)

            return substr($content,0,$p+strlen($close_tag));

        else{

            $content = substr($content,0,$p);

            $p = stripos($content,">",0);

            return substr($content,$p+1);

        }

    }

   

    





}

?>