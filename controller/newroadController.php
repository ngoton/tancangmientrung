<?php

Class newroadController Extends baseController {

    public function index() {

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        $this->view->data['lib'] = $this->lib;

        $this->view->data['title'] = 'Quản lý định mức';



        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;

            $order = isset($_POST['order']) ? $_POST['order'] : null;

            $page = isset($_POST['page']) ? $_POST['page'] : null;

            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;

            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;

            $status = isset($_POST['vong']) ? $_POST['vong'] : null;

            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;

        }

        else{

            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'road_id';

            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';

            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;

            $keyword = "";

            $limit = 20;

            $status = 1;

            $batdau = 0;
            $ketthuc = 0;

        }





        $road_model = $this->model->get('roadModel');

        $sonews = $limit;

        $x = ($page-1) * $sonews;

        $pagination_stages = 2;

        $data = array('where'=>'status='.$status);

        if ($batdau > 0 && $ketthuc > 0) {
            $data['where'] .= ' AND road_from = '.$batdau.' AND road_to = '.$ketthuc;
        }

        $tongsodong = count($road_model->getAllRoad($data));

        $tongsotrang = ceil($tongsodong / $sonews);

        



        $this->view->data['page'] = $page;

        $this->view->data['order_by'] = $order_by;

        $this->view->data['order'] = $order;

        $this->view->data['keyword'] = $keyword;

        $this->view->data['limit'] = $limit;

        $this->view->data['pagination_stages'] = $pagination_stages;

        $this->view->data['tongsotrang'] = $tongsotrang;

        $this->view->data['sonews'] = $sonews;

        $this->view->data['status'] = $status;

        $this->view->data['batdau'] = $batdau;
        $this->view->data['ketthuc'] = $ketthuc;

        $data = array(

            'order_by'=>$order_by,

            'order'=>$order,

            'limit'=>$x.','.$sonews,

            'where'=>'status='.$status,

            );

        if ($batdau > 0 && $ketthuc > 0) {
            $data['where'] .= ' AND road_from = '.$batdau.' AND road_to = '.$ketthuc;
        }

        if ($keyword != '') {

            $search = ' AND ( road_from in (SELECT warehouse_id FROM warehouse WHERE warehouse_name LIKE "%'.$keyword.'%" ) 

                        OR road_to in (SELECT warehouse_id FROM warehouse WHERE warehouse_name LIKE "%'.$keyword.'%" ) )';

            $data['where'] .= $search;

        }

        

        $warehouse_model = $this->model->get('warehouseModel');

        $warehouse = $warehouse_model->getAllWarehouse(array('order_by'=>'warehouse_name','order'=>'ASC'));

        

        $this->view->data['warehouses'] = $warehouse;



        $warehouse_data = array();

        foreach ($warehouse as $warehouse) {

            $warehouse_data['warehouse_id'][$warehouse->warehouse_id] = $warehouse->warehouse_id;

            $warehouse_data['warehouse_name'][$warehouse->warehouse_id] = $warehouse->warehouse_name;

        }

        

        $this->view->data['warehouse'] = $warehouse_data;

        

        $this->view->data['roads'] = $road_model->getAllRoad($data);



        $this->view->data['lastID'] = isset($road_model->getLastRoad()->road_id)?$road_model->getLastRoad()->road_id:0;

        

        $this->view->show('newroad/index');

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



            /**************/

            $toll_booth = $_POST['toll_booth'];

            /**************/

            $toll_booth_model = $this->model->get('tollModel');

            $bridge_cost_model = $this->model->get('bridgecostModel');



            $distance = $_POST['distance'];

            $distance_model = $this->model->get('distanceModel');





            $road = $this->model->get('roadModel');

            $data = array(

                        'road_oil' => trim($_POST['road_oil']),

                        'road_time' => trim($_POST['road_time']),

                        'road_km' => trim($_POST['road_km']),

                        'way' => trim($_POST['way']),

                        'bridge_cost' => round(trim(str_replace(',','',$_POST['bridge_cost']))/1.1),

                        'police_cost' => trim(str_replace(',','',$_POST['police_cost'])),

                        'tire_cost' => trim(str_replace(',','',$_POST['tire_cost'])),

                        'charge_add' => trim(str_replace(',','',$_POST['charge_add'])),

                        'start_time' => strtotime(trim($_POST['start_time'])),

                        'end_time' => strtotime(trim($_POST['end_time'])),

                        'status' => trim($_POST['status'])

                        );



            if ($_POST['yes'] != "") {

                //$data['road_update_user'] = $_SESSION['userid_logined'];

                //$data['road_update_time'] = time();

                //var_dump($data);

                $shipment = $this->model->get('shipmentModel');



                $road_d = $road->getRoad($_POST['yes']);



                $dm1 = $road->queryRoad('SELECT * FROM road WHERE road_from='.$road_d->road_from.' AND road_to='.$road_d->road_to.' AND start_time <= '.$data['start_time'].' AND end_time <= '.$data['end_time'].' AND end_time >= '.$data['start_time'].' ORDER BY end_time ASC LIMIT 1');

                $dm2 = $road->queryRoad('SELECT * FROM road WHERE road_from='.$road_d->road_from.' AND road_to='.$road_d->road_to.' AND end_time >= '.$data['end_time'].' AND start_time >= '.$data['start_time'].' AND start_time <= '.$data['end_time'].' ORDER BY end_time ASC LIMIT 1');

                $dm3 = $road->queryRoad('SELECT * FROM road WHERE road_from='.$road_d->road_from.' AND road_to='.$road_d->road_to.' AND start_time <= '.$data['start_time'].' AND end_time >= '.$data['end_time'].' ORDER BY end_time ASC LIMIT 1');



                if ($dm3) {

                    foreach ($dm3 as $row) {

                        $d = array(

                            'end_time' => strtotime(date('d-m-Y',strtotime($_POST['start_time'].' -1 day'))),

                            );

                        $road->updateRoad($d,array('road_id'=>$row->road_id));



                        $c = array(

                            'road_from' => $row->road_from,

                            'road_to' => $row->road_to,

                            'road_oil' => $row->road_oil,

                            'road_time' => $row->road_time,

                            'road_km' => $row->road_km,

                            'way' => $row->way,

                            'bridge_cost' => $row->bridge_cost,

                            'police_cost' => $row->police_cost,

                            'tire_cost' => $row->tire_cost,

                            'charge_add' => $row->charge_add,

                            'start_time' => strtotime(date('d-m-Y',strtotime($_POST['end_time'].' +1 day'))),

                            'end_time' => $row->end_time,

                            'status' => $row->status

                            );

                        $road->createRoad($c);



                        $last_road = $road->getLastRoad()->road_id;



                                $br = $bridge_cost_model->getAllBridgecost(array('where'=>'road='.$row->road_id));

                                foreach ($br as $v) {

                                    $data_br = array(

                                        'toll_booth' => $v->toll_booth,

                                        'toll_booth_cost' => $v->toll_booth_cost,

                                        'road' => $last_road,

                                        'check_vat' => $v->check_vat,

                                    );

                                    $bridge_cost_model->createBridgecost($data_br);

                                }



                                $dst = $distance_model->getAllDistance(array('where'=>'road='.$row->road_id));

                                foreach ($dst as $v) {

                                    $data_dst = array(

                                        'km' => $v->km,

                                        'oil' => $v->oil,

                                        'way' => $v->way,

                                        'road' => $last_road,

                                    );

                                    $distance_model->createDistance($data_dst);

                                }



                        $shipments = $shipment->getAllShipment(array('where'=>'shipment_from = '.$row->road_from.' AND shipment_to = '.$row->road_to.' AND shipment_date >= '.$data['start_time'].' AND shipment_date <= '.$data['end_time']));

                        foreach ($shipments as $ship) {

                            $data_edit = array(

                                'shipment_cost' => ($ship->shipment_cost-(($row->road_time*3000000)+$row->police_cost+round($row->bridge_cost*1.1)+$row->tire_cost+($row->road_oil*round($ship->oil_cost*1.1))))+(($data['road_time']*3000000)+$data['police_cost']+round($data['bridge_cost']*1.1)+$data['tire_cost']+($data['road_oil']*round($ship->oil_cost*1.1))),

                                'shipment_bonus' => ($ship->shipment_ton>29)?round($ship->shipment_ton-29)*$data['charge_add']:0,

                                );

                            $shipment->updateShipment($data_edit,array('shipment_id' => $ship->shipment_id));

                        }



                    }



                    $data['road_from'] = $road_d->road_from;

                    $data['road_to'] = $road_d->road_to;



                    

                    $road->createRoad($data);



                    $road_id = $road->getLastRoad()->road_id;



                            foreach ($toll_booth as $v) {

                                if (isset($v['toll_booth_id']) && $v['toll_booth_id'] != "") {

                                    $id_toll_booth = $v['toll_booth_id'];

                                    $data_toll_booth = array(

                                        'toll_name' => $v['toll_booth_name'],

                                        'toll_mst' => $v['toll_mst'],

                                        'toll_symbol' => $v['toll_symbol'],

                                        'toll_type' => $v['toll_type'],

                                    );

                                    $toll_booth_model->updateToll($data_toll_booth,array('toll_id'=>$id_toll_booth));

                                }

                                else{

                                    $data_toll_booth = array(

                                        'toll_name' => $v['toll_booth_name'],

                                        'toll_mst' => $v['toll_mst'],

                                        'toll_symbol' => $v['toll_symbol'],

                                        'toll_type' => $v['toll_type'],

                                    );

                                    $toll_booth_model->createToll($data_toll_booth);

                                    $id_toll_booth = $toll_booth_model->getLastToll()->toll_id;

                                }

                                $data_bridge_cost = array(

                                    'toll_booth' => $id_toll_booth,

                                    'road' => $road_id,

                                    'toll_booth_cost' => trim(str_replace(',','',$v['toll_booth_cost'])),

                                    'check_vat' => $v['check_vat'],

                                );



                                $bridge_cost_model->createBridgecost($data_bridge_cost);

                            }



                            foreach ($distance as $v) {

                    

                                $data_distance = array(

                                    'road' => $road_id,

                                    'km' => trim(str_replace(',','',$v['km'])),

                                    'oil' => $v['oil'],

                                    'way' => $v['way'],

                                );

                                if (!$distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $distance_model->createDistance($data_distance);

                                }

                                else if ($distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $id_distance = $distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))->distance_id;

                                    

                                    $distance_model->updateDistance($data_distance,array('distance_id'=>$id_distance));

                                }

                            }

                }

                else if ($dm1 || $dm2) {

                    if($dm1){

                        foreach ($dm1 as $row) {

                            $d = array(

                                'end_time' => strtotime(date('d-m-Y',strtotime($_POST['start_time'].' -1 day'))),

                                );

                            $road->updateRoad($d,array('road_id'=>$row->road_id));



                            $shipments = $shipment->getAllShipment(array('where'=>'shipment_from = '.$row->road_from.' AND shipment_to = '.$row->road_to.' AND shipment_date >= '.$data['start_time'].' AND shipment_date <= '.$row->end_time));

                            foreach ($shipments as $ship) {

                                $data_edit = array(

                                    'shipment_cost' => ($ship->shipment_cost-(($row->road_time*3000000)+$row->police_cost+round($row->bridge_cost*1.1)+$row->tire_cost+($row->road_oil*round($ship->oil_cost*1.1))))+(($data['road_time']*3000000)+$data['police_cost']+round($data['bridge_cost']*1.1)+$data['tire_cost']+($data['road_oil']*round($ship->oil_cost*1.1))),

                                    'shipment_bonus' => ($ship->shipment_ton>29)?round($ship->shipment_ton-29)*$data['charge_add']:0,

                                    );

                                $shipment->updateShipment($data_edit,array('shipment_id' => $ship->shipment_id));

                            }



                        }

                    }

                    if($dm2){

                        foreach ($dm2 as $row) {

                            $d = array(

                                'start_time' => strtotime(date('d-m-Y',strtotime($_POST['end_time'].' +1 day'))),

                                );

                            $road->updateRoad($d,array('road_id'=>$row->road_id));



                            $shipments = $shipment->getAllShipment(array('where'=>'shipment_from = '.$row->road_from.' AND shipment_to = '.$row->road_to.' AND shipment_date >= '.$row->start_time.' AND shipment_date <= '.$data['end_time']));

                            foreach ($shipments as $ship) {

                                $data_edit = array(

                                    'shipment_cost' => ($ship->shipment_cost-(($row->road_time*3000000)+$row->police_cost+round($row->bridge_cost*1.1)+$row->tire_cost+($row->road_oil*round($ship->oil_cost*1.1))))+(($data['road_time']*3000000)+$data['police_cost']+round($data['bridge_cost']*1.1)+$data['tire_cost']+($data['road_oil']*round($ship->oil_cost*1.1))),

                                    'shipment_bonus' => ($ship->shipment_ton>29)?round($ship->shipment_ton-29)*$data['charge_add']:0,

                                    );

                                $shipment->updateShipment($data_edit,array('shipment_id' => $ship->shipment_id));

                            }



                        }

                    }



                    $data['road_from'] = $road_d->road_from;

                    $data['road_to'] = $road_d->road_to;



                    

                    $road->createRoad($data);



                    $road_id = $road->getLastRoad()->road_id;



                        foreach ($toll_booth as $v) {

                            if (isset($v['toll_booth_id']) && $v['toll_booth_id'] != "") {

                                $id_toll_booth = $v['toll_booth_id'];

                                $data_toll_booth = array(

                                    'toll_name' => $v['toll_booth_name'],

                                    'toll_mst' => $v['toll_mst'],

                                    'toll_symbol' => $v['toll_symbol'],

                                    'toll_type' => $v['toll_type'],

                                );

                                $toll_booth_model->updateToll($data_toll_booth,array('toll_id'=>$id_toll_booth));

                            }

                            else{

                                $data_toll_booth = array(

                                    'toll_name' => $v['toll_booth_name'],

                                    'toll_mst' => $v['toll_mst'],

                                    'toll_symbol' => $v['toll_symbol'],

                                    'toll_type' => $v['toll_type'],

                                );

                                $toll_booth_model->createToll($data_toll_booth);

                                $id_toll_booth = $toll_booth_model->getLastToll()->toll_id;

                            }

                            $data_bridge_cost = array(

                                'toll_booth' => $id_toll_booth,

                                'road' => $road_id,

                                'toll_booth_cost' => trim(str_replace(',','',$v['toll_booth_cost'])),

                                'check_vat' => $v['check_vat'],

                            );



                            $bridge_cost_model->createBridgecost($data_bridge_cost);

                        }



                        foreach ($distance as $v) {

                    

                                $data_distance = array(

                                    'road' => $road_id,

                                    'km' => trim(str_replace(',','',$v['km'])),

                                    'oil' => $v['oil'],

                                    'way' => $v['way'],

                                );

                                if (!$distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $distance_model->createDistance($data_distance);

                                }

                                else if ($distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $id_distance = $distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))->distance_id;

                                    

                                    $distance_model->updateDistance($data_distance,array('distance_id'=>$id_distance));

                                }

                            }

                }



                    echo "Thêm thành công";



                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$road->getLastRoad()->road_id."|road|".implode("-",$data)."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

            }

            else{

                //$data['road_create_user'] = $_SESSION['userid_logined'];

                //$data['staff'] = $_POST['staff'];

                //var_dump($data);

                $data['road_from'] = trim($_POST['road_from']);

                $data['road_to'] = trim($_POST['road_to']);

                if ($road->getRoadByWhere(array('road_from'=>$_POST['road_from'],'road_to'=>$_POST['road_to'],'start_time'=>$data['start_time'],'end_time'=>$data['end_time']))) {

                    echo "Bảng định mức này đã tồn tại";

                    return false;

                }

                else{

                    if ($road->getRoadByWhere(array('road_from'=>$_POST['road_from'],'road_to'=>$_POST['road_to']))) {



                        $shipment = $this->model->get('shipmentModel');



                        $dm1 = $road->queryRoad('SELECT * FROM road WHERE road_from='.$data['road_from'].' AND road_to='.$data['road_to'].' AND start_time <= '.$data['start_time'].' AND end_time <= '.$data['end_time'].' AND end_time >= '.$data['start_time'].' ORDER BY end_time ASC LIMIT 1');

                        $dm2 = $road->queryRoad('SELECT * FROM road WHERE road_from='.$data['road_from'].' AND road_to='.$data['road_to'].' AND end_time >= '.$data['end_time'].' AND start_time >= '.$data['start_time'].' AND start_time <= '.$data['end_time'].' ORDER BY end_time ASC LIMIT 1');

                        $dm3 = $road->queryRoad('SELECT * FROM road WHERE road_from='.$data['road_from'].' AND road_to='.$data['road_to'].' AND start_time <= '.$data['start_time'].' AND end_time >= '.$data['end_time'].' ORDER BY end_time ASC LIMIT 1');



                        if ($dm3) {

                            foreach ($dm3 as $row) {

                                $d = array(

                                    'end_time' => strtotime(date('d-m-Y',strtotime($_POST['start_time'].' -1 day'))),

                                    );

                                $road->updateRoad($d,array('road_id'=>$row->road_id));



                                $c = array(

                                    'road_from' => $row->road_from,

                                    'road_to' => $row->road_to,

                                    'road_oil' => $row->road_oil,

                                    'road_time' => $row->road_time,

                                    'road_km' => $row->road_km,

                                    'way' => $row->way,

                                    'bridge_cost' => $row->bridge_cost,

                                    'police_cost' => $row->police_cost,

                                    'tire_cost' => $row->tire_cost,

                                    'charge_add' => $row->charge_add,

                                    'start_time' => strtotime(date('d-m-Y',strtotime($_POST['end_time'].' +1 day'))),

                                    'end_time' => $row->end_time,

                                    'status' => $row->status

                                    );

                                $road->createRoad($c);



                                $last_road = $road->getLastRoad()->road_id;



                                $br = $bridge_cost_model->getAllBridgecost(array('where'=>'road='.$row->road_id));

                                foreach ($br as $v) {

                                    $data_br = array(

                                        'toll_booth' => $v->toll_booth,

                                        'toll_booth_cost' => $v->toll_booth_cost,

                                        'road' => $last_road,

                                        'check_vat' => $v->check_vat,

                                    );

                                    $bridge_cost_model->createBridgecost($data_br);

                                }



                                $dst = $distance_model->getAllDistance(array('where'=>'road='.$row->road_id));

                                foreach ($dst as $v) {

                                    $data_dst = array(

                                        'km' => $v->km,

                                        'oil' => $v->oil,

                                        'way' => $v->way,

                                        'road' => $last_road,

                                    );

                                    $distance_model->createDistance($data_dst);

                                }



                                $shipments = $shipment->getAllShipment(array('where'=>'shipment_from = '.$row->road_from.' AND shipment_to = '.$row->road_to.' AND shipment_date >= '.$data['start_time'].' AND shipment_date <= '.$data['end_time']));

                                foreach ($shipments as $ship) {

                                    $data_edit = array(

                                        'shipment_cost' => ($ship->shipment_cost-(($row->road_time*3000000)+$row->police_cost+round($row->bridge_cost*1.1)+$row->tire_cost+($row->road_oil*round($ship->oil_cost*1.1))))+(($data['road_time']*3000000)+$data['police_cost']+round($data['bridge_cost']*1.1)+$data['tire_cost']+($data['road_oil']*round($ship->oil_cost*1.1))),

                                        );

                                    $shipment->updateShipment($data_edit,array('shipment_id' => $ship->shipment_id));

                                }



                            }



                            



                            

                            $road->createRoad($data);



                            $road_id = $road->getLastRoad()->road_id;



                            foreach ($toll_booth as $v) {

                                if (isset($v['toll_booth_id']) && $v['toll_booth_id'] != "") {

                                    $id_toll_booth = $v['toll_booth_id'];

                                }

                                else{

                                    $data_toll_booth = array(

                                        'toll_name' => $v['toll_booth_name'],

                                        'toll_mst' => $v['toll_mst'],

                                        'toll_symbol' => $v['toll_symbol'],

                                        'toll_type' => $v['toll_type'],

                                    );

                                    $toll_booth_model->createToll($data_toll_booth);

                                    $id_toll_booth = $toll_booth_model->getLastToll()->toll_id;

                                }

                                $data_bridge_cost = array(

                                    'toll_booth' => $id_toll_booth,

                                    'road' => $road_id,

                                    'toll_booth_cost' => trim(str_replace(',','',$v['toll_booth_cost'])),

                                    'check_vat' => $v['check_vat'],

                                );



                                $bridge_cost_model->createBridgecost($data_bridge_cost);

                            }



                            foreach ($distance as $v) {

                    

                                $data_distance = array(

                                    'road' => $road_id,

                                    'km' => trim(str_replace(',','',$v['km'])),

                                    'oil' => $v['oil'],

                                    'way' => $v['way'],

                                );

                                if (!$distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $distance_model->createDistance($data_distance);

                                }

                                else if ($distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $id_distance = $distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))->distance_id;

                                    

                                    $distance_model->updateDistance($data_distance,array('distance_id'=>$id_distance));

                                }

                            }



                        }

                        else if ($dm1 || $dm2) {

                            if($dm1){

                                foreach ($dm1 as $row) {

                                    $d = array(

                                        'end_time' => strtotime(date('d-m-Y',strtotime($_POST['start_time'].' -1 day'))),

                                        );

                                    $road->updateRoad($d,array('road_id'=>$row->road_id));



                                    $shipments = $shipment->getAllShipment(array('where'=>'shipment_from = '.$row->road_from.' AND shipment_to = '.$row->road_to.' AND shipment_date >= '.$data['start_time'].' AND shipment_date <= '.$row->end_time));

                                    foreach ($shipments as $ship) {

                                        $data_edit = array(

                                            'shipment_cost' => ($ship->shipment_cost-(($row->road_time*3000000)+$row->police_cost+round($row->bridge_cost*1.1)+$row->tire_cost+($row->road_oil*round($ship->oil_cost*1.1))))+(($data['road_time']*3000000)+$data['police_cost']+round($data['bridge_cost']*1.1)+$data['tire_cost']+($data['road_oil']*round($ship->oil_cost*1.1))),

                                            );

                                        $shipment->updateShipment($data_edit,array('shipment_id' => $ship->shipment_id));

                                    }



                                }

                            }

                            if($dm2){

                                foreach ($dm2 as $row) {

                                    $d = array(

                                        'start_time' => strtotime(date('d-m-Y',strtotime($_POST['end_time'].' +1 day'))),

                                        );

                                    $road->updateRoad($d,array('road_id'=>$row->road_id));



                                    $shipments = $shipment->getAllShipment(array('where'=>'shipment_from = '.$row->road_from.' AND shipment_to = '.$row->road_to.' AND shipment_date >= '.$row->start_time.' AND shipment_date <= '.$data['end_time']));

                                    foreach ($shipments as $ship) {

                                        $data_edit = array(

                                            'shipment_cost' => ($ship->shipment_cost-(($row->road_time*3000000)+$row->police_cost+round($row->bridge_cost*1.1)+$row->tire_cost+($row->road_oil*round($ship->oil_cost*1.1))))+(($data['road_time']*3000000)+$data['police_cost']+round($data['bridge_cost']*1.1)+$data['tire_cost']+($data['road_oil']*round($ship->oil_cost*1.1))),

                                            );

                                        $shipment->updateShipment($data_edit,array('shipment_id' => $ship->shipment_id));

                                    }



                                }

                            }





                            

                            $road->createRoad($data);



                            $road_id = $road->getLastRoad()->road_id;



                            foreach ($toll_booth as $v) {

                                if (isset($v['toll_booth_id']) && $v['toll_booth_id'] != "") {

                                    $id_toll_booth = $v['toll_booth_id'];

                                }

                                else{

                                    $data_toll_booth = array(

                                        'toll_name' => $v['toll_booth_name'],

                                        'toll_mst' => $v['toll_mst'],

                                        'toll_symbol' => $v['toll_symbol'],

                                        'toll_type' => $v['toll_type'],

                                    );

                                    $toll_booth_model->createToll($data_toll_booth);

                                    $id_toll_booth = $toll_booth_model->getLastToll()->toll_id;

                                }

                                $data_bridge_cost = array(

                                    'toll_booth' => $id_toll_booth,

                                    'road' => $road_id,

                                    'toll_booth_cost' => trim(str_replace(',','',$v['toll_booth_cost'])),

                                    'check_vat' => $v['check_vat'],

                                );



                                $bridge_cost_model->createBridgecost($data_bridge_cost);

                            }



                            foreach ($distance as $v) {

                    

                                $data_distance = array(

                                    'road' => $road_id,

                                    'km' => trim(str_replace(',','',$v['km'])),

                                    'oil' => $v['oil'],

                                    'way' => $v['way'],

                                );

                                if (!$distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $distance_model->createDistance($data_distance);

                                }

                                else if ($distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $id_distance = $distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))->distance_id;

                                    

                                    $distance_model->updateDistance($data_distance,array('distance_id'=>$id_distance));

                                }

                            }

                        }

                    }

                    else{

                        $road->createRoad($data);



                        $road_id = $road->getLastRoad()->road_id;



                        foreach ($toll_booth as $v) {

                            if (isset($v['toll_booth_id']) && $v['toll_booth_id'] != "") {

                                $id_toll_booth = $v['toll_booth_id'];

                            }

                            else{

                                $data_toll_booth = array(

                                    'toll_name' => $v['toll_booth_name'],

                                    'toll_mst' => $v['toll_mst'],

                                    'toll_symbol' => $v['toll_symbol'],

                                    'toll_type' => $v['toll_type'],

                                );

                                $toll_booth_model->createToll($data_toll_booth);

                                $id_toll_booth = $toll_booth_model->getLastToll()->toll_id;

                            }

                            $data_bridge_cost = array(

                                'toll_booth' => $id_toll_booth,

                                'road' => $road_id,

                                'toll_booth_cost' => trim(str_replace(',','',$v['toll_booth_cost'])),

                                'check_vat' => $v['check_vat'],

                            );



                            $bridge_cost_model->createBridgecost($data_bridge_cost);

                        }



                        foreach ($distance as $v) {

                    

                                $data_distance = array(

                                    'road' => $road_id,

                                    'km' => trim(str_replace(',','',$v['km'])),

                                    'oil' => $v['oil'],

                                    'way' => $v['way'],

                                );

                                if (!$distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $distance_model->createDistance($data_distance);

                                }

                                else if ($distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))) {

                                    $id_distance = $distance_model->getDistanceByWhere(array('road'=>$road_id,'way'=>(string)$data_distance['way']))->distance_id;

                                    

                                    $distance_model->updateDistance($data_distance,array('distance_id'=>$id_distance));

                                }

                            }

                    }



                    echo "Thêm thành công";



                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$road->getLastRoad()->road_id."|road|".implode("-",$data)."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                }

                

            }

                    

        }

    }



    public function getroadfrom(){

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

                'where'=>'(check_new IS NULL)',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            else{

                $data = array(

                'where'=>'( warehouse_name LIKE "%'.$_POST['keyword'].'%" AND check_new IS NULL)',

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

                echo '<li onclick="set_item_road_from(\''.$rs->warehouse_id.'\',\''.$rs->warehouse_name.'\')">'.$warehouse_name.'</li>';

            }

        }

    }



    public function getroadto(){

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

                'where'=>'(check_new IS NULL)',

                );

                $list = $warehouse_model->getAllWarehouse($data);

            }

            else{

                $data = array(

                'where'=>'( warehouse_name LIKE "%'.$_POST['keyword'].'%" AND check_new IS NULL)',

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

                echo '<li onclick="set_item_road_to(\''.$rs->warehouse_id.'\',\''.$rs->warehouse_name.'\')">'.$warehouse_name.'</li>';

            }

        }

    }

    



    public function delete(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3  && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $road = $this->model->get('roadModel');

            if (isset($_POST['xoa'])) {

                $data = explode(',', $_POST['xoa']);

                foreach ($data as $data) {

                    $road->deleteRoad($data);



                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|road|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                }

                return true;

            }

            else{



                date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|road|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);



                return $road->deleteRoad($_POST['data']);

            }

            

        }

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



            $road = $this->model->get('roadModel');

            $warehouse = $this->model->get('warehouseModel');



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

                        //$val[] = $cell->getValue();

                        $val[] = is_numeric($cell->getCalculatedValue()) ? round($cell->getCalculatedValue()) : $cell->getCalculatedValue();

                        //here's my prob..

                        //echo $val;

                    }

                    if ($val[1] != null && $val[2] != null && $val[3] != null && $val[4] != null && $val[5] != null) {



                            if($warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[1])))) {

                                $id_from = $warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[1])))->warehouse_id;

                            }

                            else if(!$warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[1])))){

                                $warehouse_data_from = array(

                                    'warehouse_name' => trim($val[1]),

                                    );

                                $warehouse->createWarehouse($warehouse_data_from);



                                $id_from = $warehouse->getLastWarehouse()->warehouse_id;

                            }



                            if($warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[2])))) {

                                $id_to = $warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[2])))->warehouse_id;

                            }

                            else if(!$warehouse->getWarehouseByWhere(array('warehouse_name'=>trim($val[2])))){

                                $warehouse_data_to = array(

                                    'warehouse_name' => trim($val[2]),

                                    );

                                $warehouse->createWarehouse($warehouse_data_to);



                                $id_to = $warehouse->getLastWarehouse()->warehouse_id;

                            }



                            if ($id_from != null && $id_to != null) {

                                $chieu = (trim($val[3])=="Lên")?1:((trim($val[3])=="Xuống")?2:((trim($val[3])=="Bằng")?3:((trim($val[3])=="ĐN-QN")?4:0)));

                                if($road->getRoadByWhere(array('road_from'=>$id_from,'road_to'=>$id_to))) {

                                    $id_road = $road->getRoadByWhere(array('road_from'=>$id_from,'road_to'=>$id_to))->road_id;



                                    $road_data = array(

                                    'way' => $chieu,

                                    'road_km' => trim($val[4]),

                                    'road_oil' => trim($val[5]),

                                    'bridge_cost' => round(trim($val[6])/1.1),

                                    'police_cost' => trim($val[7]),

                                    'tire_cost' => trim($val[8]),

                                    'charge_add' => trim($val[9]),

                                    'road_time' => trim($val[10]),

                                    );

                                    $road->updateRoad($road_data,array('road_id' => $id_road));

                                }

                                else{

                                    $road_data = array(

                                    'road_from' => $id_from,

                                    'road_to' => $id_to,

                                    'way' => $chieu,

                                    'road_km' => trim($val[4]),

                                    'road_oil' => trim($val[5]),

                                    'bridge_cost' => round(trim($val[6])/1.1),

                                    'police_cost' => trim($val[7]),

                                    'tire_cost' => trim($val[8]),

                                    'charge_add' => trim($val[9]),

                                    'road_time' => trim($val[10]),

                                    );

                                    $road->createRoad($road_data);

                                }

                            }

                        

                    }

                    

                    //var_dump($this->getNameDistrict($this->lib->stripUnicode($val[1])));

                    // insert





                }

                //return $this->view->redirect('transport');

            

            return $this->view->redirect('road');

        }

        $this->view->show('road/import');



    }

    



    public function view() {

        

        $this->view->show('handling/view');

    }

    function export(){

        $this->view->disableLayout();

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        

            $warehouse_model = $this->model->get('warehouseModel');

            $road_model = $this->model->get('roadModel');

            

            $road = $road_model->getAllRoad();





            require("lib/Classes/PHPExcel/IOFactory.php");

            require("lib/Classes/PHPExcel.php");



            $objPHPExcel = new PHPExcel();



            



            $index_worksheet = 0; //(worksheet mặc định là 0, nếu tạo nhiều worksheet $index_worksheet += 1)

            $objPHPExcel->setActiveSheetIndex($index_worksheet)

               ->setCellValue('A1', 'STT')

               ->setCellValue('B1', 'Kho đi')

               ->setCellValue('C1', 'Kho đến')

               ->setCellValue('D1', 'Chiều đi')

               ->setCellValue('E1', 'Khoảng cách')

               ->setCellValue('F1', 'Lit dầu')

               ->setCellValue('G1', 'Cầu đường')

               ->setCellValue('H1', 'Công an')

               ->setCellValue('I1', 'Vá vỏ')

               ->setCellValue('J1', 'Cước vượt tải')

               ->setCellValue('K1', 'Bồi dưỡng kho đi')

               ->setCellValue('L1', 'Bồi dưỡng kho đến')

               ->setCellValue('M1', 'Cân xe')

               ->setCellValue('N1', 'Quét cont')

               ->setCellValue('O1', 'Vé cổng');

               



            



            

            

            



            if ($road) {



                $hang = 2;

                $i=1;



                $kho_data = array();

                foreach ($road as $row) {

                    $tongboiduongdi = 0;

                    $tongboiduongden = 0;

                    $canxe = 0;

                    $quetcont = 0;

                    $vecong = 0;

                    $khodi = $warehouse_model->getAllWarehouse(array('where'=> 'warehouse_id = '.$row->road_from)); 

                    foreach ($khodi as $ware) {

                        $kho_data[$ware->warehouse_id] = $ware->warehouse_name;

                        $tongboiduongdi += $ware->warehouse_add + $ware->warehouse_ton;

                        $canxe += $ware->warehouse_weight;

                        $quetcont += $ware->warehouse_clean;

                        $vecong += $ware->warehouse_gate;

                    }



                    $khoden = $warehouse_model->getAllWarehouse(array('where'=> 'warehouse_id = '.$row->road_to)); 

                    foreach ($khoden as $ware2) {

                        $kho_data[$ware2->warehouse_id] = $ware2->warehouse_name;

                        $tongboiduongden += $ware2->warehouse_add + $ware2->warehouse_ton;

                        $canxe += $ware2->warehouse_weight;

                        $quetcont += $ware2->warehouse_clean;

                        $vecong += $ware2->warehouse_gate;

                    }





                    //$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$hang)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );

                     $objPHPExcel->setActiveSheetIndex(0)

                        ->setCellValue('A' . $hang, $i++)

                        ->setCellValueExplicit('B' . $hang, $kho_data[$row->road_from])

                        ->setCellValue('C' . $hang, $kho_data[$row->road_to])

                        ->setCellValue('D' . $hang, $row->way==0?"Rỗng":($row->way==1?"Lên":($row->way==2?"Xuống":($row->way==3?"Bằng":"ĐN-QN"))))

                        ->setCellValue('E' . $hang, $row->road_km)

                        ->setCellValue('F' . $hang, $row->road_oil)

                        ->setCellValue('G' . $hang, $row->bridge_cost)

                        ->setCellValue('H' . $hang, $row->police_cost)

                        ->setCellValue('I' . $hang, $row->tire_cost)

                        ->setCellValue('J' . $hang, $row->charge_add)

                        ->setCellValue('K' . $hang, $tongboiduongdi)

                        ->setCellValue('L' . $hang, $tongboiduongden)

                        ->setCellValue('M' . $hang, $canxe)

                        ->setCellValue('N' . $hang, $quetcont)

                        ->setCellValue('O' . $hang, $vecong);

                     $hang++;





                  }



          }



            $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();



            $highestRow ++;





            $objPHPExcel->getActiveSheet()->getStyle('E2:O'.$highestRow)->getNumberFormat()->setFormatCode("#,##0_);[Black](#,##0)");

            $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFont()->setBold(true);

            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(16);

            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(14);

            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);

            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);



            // Set properties

            $objPHPExcel->getProperties()->setCreator("TCMT")

                            ->setLastModifiedBy($_SESSION['user_logined'])

                            ->setTitle("Road Report")

                            ->setSubject("Road Report")

                            ->setDescription("Road Report.")

                            ->setKeywords("Road Report")

                            ->setCategory("Road Report");

            $objPHPExcel->getActiveSheet()->setTitle("Bang dinh muc");



            $objPHPExcel->getActiveSheet()->freezePane('A1');

            $objPHPExcel->setActiveSheetIndex(0);







            



            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');



            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

            header("Content-Disposition: attachment; filename= BẢNG ĐỊNH MỨC.xlsx");

            header("Cache-Control: max-age=0");

            ob_clean();

            $objWriter->save("php://output");

        

    }



}

?>