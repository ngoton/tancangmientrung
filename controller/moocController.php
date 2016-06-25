<?php
Class moocController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 5) {
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
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $xe = isset($_POST['xe']) ? $_POST['xe'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'vehicle_number';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
            $batdau = "";
            $xe = "";
        }
        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();

        $this->view->data['vehicles'] = $vehicles;


        $join = array('table'=>'vehicle, tire','where'=>'vehicle.vehicle_id = equipment.vehicle AND tire.tire_id=equipment.tire_in');

        $equipment_model = $this->model->get('equipmentModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;

        $data = array(
            'where' => 'tire.tire_status=1 AND tire.tire_position=2 AND own=1',
            );
        if($xe != ""){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }
        if($batdau != ""){
            $data['where'] = $data['where'].' AND equipment_date = '.strtotime($batdau);
        }
        
        $tongsodong = count($equipment_model->getAllEquipment($data,$join));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['limit'] = $limit;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['sonews'] = $sonews;
        $this->view->data['xe'] = $xe;
        $this->view->data['batdau'] = $batdau;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => '1=1',
            );
        
        if($xe != ""){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }
        if($batdau != ""){
            $data['where'] = $data['where'].' AND equipment_date = '.strtotime($batdau);
        }

        if ($keyword != '') {
            $search = '( vehicle_number LIKE "%'.$keyword.'%" 
                        OR tire_in in (SELECT tire_id FROM tire WHERE tire_serie LIKE "%'.$keyword.'%") 
                        OR tire_out in (SELECT tire_id FROM tire WHERE tire_serie LIKE "%'.$keyword.'%")
                )';
            $data['where'] = $search;
        }
        
        
        
        $this->view->data['equipments'] = $equipment_model->getAllEquipment($data,$join);

        $this->view->data['lastID'] = isset($equipment_model->getLastEquipment()->equipment_id)?$equipment_model->getLastEquipment()->equipment_id:0;
        
        $this->view->show('mooc/index');
    }

  

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 5) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            $equipment = $this->model->get('equipmentModel');
            $tire = $this->model->get('tireModel');
            $data = array(
                        'equipment_date' => strtotime(trim($_POST['equipment_date'])),
                        'vehicle_out' => trim($_POST['vehicle_out']),
                        'vehicle_km1' => trim(str_replace(',','',$_POST['vehicle_km1'])),
                        'vehicle_km2' => trim(str_replace(',','',$_POST['vehicle_km2'])),
                        'vehicle_in' => trim($_POST['vehicle_in']),
                        );

            if ($_POST['yes'] != "") {
                //$data['equipment_update_user'] = $_SESSION['userid_logined'];
                //$data['equipment_update_time'] = time();
                //var_dump($data);
                
            }
            else{
                //$data['equipment_create_user'] = $_SESSION['userid_logined'];
                //$data['staff'] = $_POST['staff'];
                //var_dump($data);
                
                    $data['approve'] = 0;

                    $vehicle1 = array();
                    $vehicle2 = array();

                    $join = array('table'=>'vehicle, tire','where'=>'vehicle.vehicle_id = equipment.vehicle AND tire.tire_id=equipment.tire_in');
                    $dataw = array(
                            'where' => 'tire.tire_status=1 AND tire.tire_position=2 AND own=1 AND vehicle='.$data['vehicle_in'],
                            );
                    $vehicle_ins = $equipment->getAllEquipment($dataw,$join);


                    foreach ($vehicle_ins as $ve) {
                        $vehicle1['out'][] = $ve->tire_in;
                        $vehicle2['in'][] = $ve->tire_in;
                        $vehicle1['km'][] = $ve->vehicle_km;
                    }

                    $join = array('table'=>'vehicle, tire','where'=>'vehicle.vehicle_id = equipment.vehicle AND tire.tire_id=equipment.tire_in');
                    $dataw = array(
                            'where' => 'tire.tire_status=1 AND tire.tire_position=2 AND own=1 AND vehicle='.$data['vehicle_out'],
                            );
                    $vehicle_outs = $equipment->getAllEquipment($dataw,$join);
                    

                    foreach ($vehicle_outs as $ve) {
                        $vehicle1['in'][] = $ve->tire_in;
                        $vehicle2['out'][] = $ve->tire_in;
                        $vehicle2['km'][] = $ve->vehicle_km;
                    }

                    $length = count($vehicle1['in']) > count($vehicle2['in']) ? count($vehicle1['in']) : count($vehicle2['in']);
                    for ($i=0; $i < $length; $i++) { 

                        $equipment->updateEquipment(array('own'=> 0),array('vehicle' => $data['vehicle_out'],'tire_in'=>$vehicle2['out'][$i]));

                        $arr = array(
                            'equipment_date' => $data['equipment_date'],
                            'vehicle' => $data['vehicle_in'],
                            'vehicle_km' => $data['vehicle_km1'],
                            'tire_in' => $vehicle1['in'][$i],
                            'tire_out' => $vehicle1['out'][$i],
                            'km_run' => $data['vehicle_km2']-$vehicle2['km'][$i],
                            'own' => 1,
                        );
                        $equipment->createEquipment($arr);

                        $equipment->updateEquipment(array('own'=> 0),array('vehicle' => $data['vehicle_in'],'tire_in'=>$vehicle1['out'][$i]));

                        $arr2 = array(
                            'equipment_date' => $data['equipment_date'],
                            'vehicle' => $data['vehicle_out'],
                            'vehicle_km' => $data['vehicle_km2'],
                            'tire_in' => $vehicle2['in'][$i],
                            'tire_out' => $vehicle2['out'][$i],
                            'km_run' => $data['vehicle_km1']-$vehicle1['km'][$i],
                            'own' => 1,
                        );
                        $equipment->createEquipment($arr2);

                    }

                    
                    echo "Thêm thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$equipment->getLastEquipment()->equipment_id."|equipment|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                
                
            }
                    
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

            $equipment = $this->model->get('equipmentModel');
            $equipment_data = $equipment->getEquipment($_POST['data']);

            $data = array(
                        
                        'approve' => 1,
                        'user_approve' => $_SESSION['userid_logined'],
                        );
          
            $equipment->updateEquipment($data,array('equipment_id' => $_POST['data']));

            date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."approve"."|".$_POST['data']."|equipment|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

            return true;
                    
        }
    }
    

    public function delete(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1  && $_SESSION['role_logined'] != 5) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $equipment = $this->model->get('equipmentModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $equipment->deleteEquipment($data);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|equipment|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                return true;
            }
            else{

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|equipment|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

                return $equipment->deleteEquipment($_POST['data']);
            }
            
        }
    }

    
    public function import(){
        $this->view->disableLayout();
        header('Content-Type: text/html; charset=utf-8');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 5 ) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_FILES['import']['name'] != null) {

            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $equipment = $this->model->get('equipmentModel');
            $vehicle = $this->model->get('vehicleModel');
            $tire = $this->model->get('tireModel');

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
            
             

                for ($row = 2; $row <= $highestRow; ++ $row) {
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
                    if ($val[1] != null && $val[2] != null ) {

                            if (!$vehicle->getVehicleByWhere(array('vehicle_number'=>trim($val[1])))) {
                                $v_data = array(
                                    'vehicle_number' => trim($val[1]),
                                    );
                                $vehicle->createVehicle($v_data);
                                $vehicle_id = $vehicle->getLastVehicle()->vehicle_id;
                            }
                            else if ($vehicle->getVehicleByWhere(array('vehicle_number'=>trim($val[1])))) {
                                $vehicle_id = $vehicle->getVehicleByWhere(array('vehicle_number'=>trim($val[1])))->vehicle_id;
                            }

                            if (!$tire->getTireByWhere(array('tire_serie'=>trim($val[2])))) {
                                $in_data = array(
                                    'tire_serie' => trim($val[2]),
                                    );
                                $tire->createTire($in_data);
                                $tire_in_id = $tire->getLastTire()->tire_id;
                            }
                            else if ($tire->getTireByWhere(array('tire_serie'=>trim($val[2])))) {
                                $tire_in_id = $tire->getTireByWhere(array('tire_serie'=>trim($val[2])))->tire_id;
                            }
                            if (trim($val[3]) == null) {
                                $tire_out_id = 0;
                            }
                            elseif (trim($val[3]) != null) {
                                if (!$tire->getTireByWhere(array('tire_serie'=>trim($val[3])))) {
                                $out_data = array(
                                    'tire_serie' => trim($val[3]),
                                    );
                                $tire->createTire($out_data);
                                $tire_out_id = $tire->getLastTire()->tire_id;
                                }
                                else if ($tire->getTireByWhere(array('tire_serie'=>trim($val[3])))) {
                                    $tire_out_id = $tire->getTireByWhere(array('tire_serie'=>trim($val[3])))->tire_id;
                                }
                            }
                            
                            if (is_numeric(trim($val[0]))) {
                                $ngaythang = PHPExcel_Shared_Date::ExcelToPHP(trim($val[0]));
                                $ngaythang = $ngaythang-3600;
                            }
                            elseif (!is_numeric(trim($val[0]))) {
                                $ngaythang = strtotime(str_replace("/", "-", trim($val[0])));
                            }
                            //$ngaythang = PHPExcel_Shared_Date::ExcelToPHP(trim($val[0]));                                      
                            

                            if(!$equipment->getEquipmentByWhere(array('equipment_date' => $ngaythang,'vehicle'=>$vehicle_id,'tire_in'=>$tire_in_id,'tire_out'=>$tire_out_id))) {
                                $equipment_data = array(
                                'equipment_date' => $ngaythang,
                                'vehicle' => $vehicle_id,
                                'tire_in' => $tire_in_id,
                                'tire_out' => $tire_out_id,
                                'vehicle_km' => trim($val[4]),
                                );
                                $equipment->createEquipment($equipment_data);
                            }
                            else if($equipment->getEquipmentByWhere(array('equipment_date' => $ngaythang,'vehicle'=>$vehicle_id,'tire_in'=>$tire_in_id,'tire_out'=>$tire_out_id))){
                                $id_equipment = $equipment->getEquipmentByWhere(array('equipment_date' => $ngaythang,'vehicle'=>$vehicle_id,'tire_in'=>$tire_in_id,'tire_out'=>$tire_out_id))->equipment_id;
                                $equipment_data = array(
                                'equipment_date' => $ngaythang,
                                'vehicle' => $vehicle_id,
                                'tire_in' => $tire_in_id,
                                'tire_out' => $tire_out_id,
                                'vehicle_km' => trim($val[4]),
                                );
                                $equipment->updateEquipment($equipment_data,array('equipment_id' => $id_equipment));
                            }


                        
                    }
                    
                    //var_dump($this->getNameDistrict($this->lib->stripUnicode($val[1])));
                    // insert


                }
                //return $this->view->redirect('transport');
            
            return $this->view->redirect('equipment');
        }
        $this->view->show('equipment/import');

    }

    public function view() {
        
        $this->view->show('equipment/view');
    }

}
?>