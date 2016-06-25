<?php

Class vehicleworkController Extends baseController {

    public function index() {

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8 && $_SESSION['role_logined'] != 6 && $_SESSION['role_logined'] != 10 && $_SESSION['role_logined'] != 4 && $_SESSION['role_logined'] != 5) {

            return $this->view->redirect('user/login');

        }

        $this->view->data['lib'] = $this->lib;

        $this->view->data['title'] = 'Quản lý hoạt động xe';



        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;

            $order = isset($_POST['order']) ? $_POST['order'] : null;

            $page = isset($_POST['page']) ? $_POST['page'] : null;

            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;

            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;

        }

        else{

            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'vehicle_number ASC, start_work';

            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';

            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;

            $keyword = "";

            $limit = 20;

        }



        $vehicle_model = $this->model->get('vehicleModel');

        $vehicles = $vehicle_model->getAllVehicle();

        $this->view->data['vehicles'] = $vehicles;



        $join = array('table'=>'vehicle','where'=>'vehicle.vehicle_id = vehicle_work.vehicle');



        $vehicle_work_model = $this->model->get('vehicleworkModel');

        $sonews = $limit;

        $x = ($page-1) * $sonews;

        $pagination_stages = 2;

        

        $tongsodong = count($vehicle_work_model->getAllVehicle(null,$join));

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

            $search = '( vehicle_number LIKE "%'.$keyword.'%"  )';

            $data['where'] = $search;

        }

        

        

        

        $this->view->data['works'] = $vehicle_work_model->getAllVehicle($data,$join);



        $this->view->data['lastID'] = isset($vehicle_work_model->getLastVehicle()->vehicle_work_id)?$vehicle_work_model->getLastVehicle()->vehicle_work_id:0;

        

        $this->view->show('vehiclework/index');

    }




    public function add(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8 && $_SESSION['role_logined'] != 10 && $_SESSION['role_logined'] != 4 && $_SESSION['role_logined'] != 5) {

            return $this->view->redirect('user/login');

        }

        if (isset($_POST['yes'])) {

            $vehicle_work_model = $this->model->get('vehicleworkModel');

            $data = array(


                        'vehicle' => trim($_POST['vehicle']),

                        'start_work' => strtotime(trim($_POST['start_work'])),

                        'end_work' => strtotime(trim($_POST['end_work'])),

                        );

            if ($_POST['yes'] != "") {
                
                    $vehicle_d = $vehicle_work_model->getVehicle($_POST['yes']);

                    $vehicle1 = $vehicle_work_model->getVehicleByWhere(array('vehicle'=>$vehicle_d->vehicle,'end_work'=>(strtotime(date('d-m-Y',strtotime(date('d-m-Y',$vehicle_d->start_work).' -1 day'))))));
                    $vehicle2 = $vehicle_work_model->getVehicleByWhere(array('vehicle'=>$vehicle_d->vehicle,'start_work'=>(strtotime(date('d-m-Y',strtotime(date('d-m-Y',$vehicle_d->end_work).' +1 day'))))));
                    if($vehicle1)
                        $vehicle_work_model->updateVehicle(array('vehicle'=>$vehicle_d->vehicle,'end_work'=>(strtotime(date('d-m-Y',strtotime($_POST['start_work'].' -1 day'))))),array('vehicle_work_id' => $vehicle1->vehicle_work_id));
                    if($vehicle2)
                        $vehicle_work_model->updateVehicle(array('vehicle'=>$vehicle_d->vehicle,'start_work'=>(strtotime(date('d-m-Y',strtotime($_POST['end_work'].' +1 day'))))),array('vehicle_work_id' => $vehicle2->vehicle_work_id));


                    $vehicle_work_model->updateVehicle($data,array('vehicle_work_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|vehicle_work|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                
                
            }
            else{
                
                $dm1 = $vehicle_work_model->queryVehicle('SELECT * FROM vehicle_work WHERE vehicle='.$data['vehicle'].' AND start_work <= '.$data['start_work'].' AND end_work <= '.$data['end_work'].' AND end_work >= '.$data['start_work'].' ORDER BY end_work ASC LIMIT 1');
                $dm2 = $vehicle_work_model->queryVehicle('SELECT * FROM vehicle_work WHERE vehicle='.$data['vehicle'].' AND end_work >= '.$data['end_work'].' AND start_work >= '.$data['start_work'].' AND start_work <= '.$data['end_work'].' ORDER BY end_work ASC LIMIT 1');
                $dm3 = $vehicle_work_model->queryVehicle('SELECT * FROM vehicle_work WHERE Vehicle='.$data['vehicle'].' AND start_work <= '.$data['start_work'].' AND end_work >= '.$data['end_work'].' ORDER BY end_work ASC LIMIT 1');

                if ($dm3) {
                            foreach ($dm3 as $row) {
                                $d = array(
                                    'end_work' => strtotime(date('d-m-Y',strtotime($_POST['start_work'].' -1 day'))),
                                    );
                                $vehicle_work_model->updateVehicle($d,array('vehicle_work_id'=>$row->vehicle_work_id));

                                $c = array(
                                    'vehicle' => $row->vehicle,
                                    'start_work' => strtotime(date('d-m-Y',strtotime($_POST['end_work'].' +1 day'))),
                                    'end_work' => $row->end_work,
                                    );
                                $vehicle_work_model->createVehicle($c);

                            }

                            

                            
                            $vehicle_work_model->createVehicle($data);

                        }
                        else if ($dm1 || $dm2) {
                            if($dm1){
                                foreach ($dm1 as $row) {
                                    $d = array(
                                        'end_work' => strtotime(date('d-m-Y',strtotime($_POST['start_work'].' -1 day'))),
                                        );
                                    $vehicle_work_model->updateVehicle($d,array('vehicle_work_id'=>$row->vehicle_work_id));

                                    
                                }
                            }
                            if($dm2){
                                foreach ($dm2 as $row) {
                                    $d = array(
                                        'start_work' => strtotime(date('d-m-Y',strtotime($_POST['end_work'].' +1 day'))),
                                        );
                                    $vehicle_work_model->updateVehicle($d,array('vehicle_work_id'=>$row->vehicle_work_id));


                                }
                            }


                            
                            $vehicle_work_model->createVehicle($data);

                        
                    }
                    else{
                        $vehicle_work_model->createVehicle($data);

                    }

                    
                    echo "Thêm thành công";

                 

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$vehicle_work_model->getLastVehicle()->vehicle_work_id."|vehicle_work|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                
                
            }

                    

        }

    }



    

    



    public function delete(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8 && $_SESSION['role_logined'] != 10 && $_SESSION['role_logined'] != 4 && $_SESSION['role_logined'] != 5) {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $vehicle = $this->model->get('vehicleworkModel');

            if (isset($_POST['xoa'])) {

                $data = explode(',', $_POST['xoa']);

                foreach ($data as $data) {

                    $vehicle->deleteVehicle($data);



                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|vehicle_work|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                }

                return true;

            }

            else{



                date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|vehicle_work|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);



                return $vehicle->deleteVehicle($_POST['data']);

            }

            

        }

    }



    

}

?>