<?php
Class driverController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8 && $_SESSION['role_logined'] != 6 && $_SESSION['role_logined'] != 10 && $_SESSION['role_logined'] != 4 && $_SESSION['role_logined'] != 5) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý tài xế';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'driver_name';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
        }

        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();
        $this->view->data['vehicles'] = $vehicles;

        $join = array('table'=>'vehicle','where'=>'vehicle.vehicle_id = driver.vehicle');

        $driver_model = $this->model->get('driverModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $tongsodong = count($driver_model->getAllDriver(null,$join));
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
            $search = '( vehicle_number LIKE "%'.$keyword.'%" OR driver_name LIKE "%'.$keyword.'%" OR driver_phone LIKE "%'.$keyword.'%" )';
            $data['where'] = $search;
        }
        
        
        
        $this->view->data['drivers'] = $driver_model->getAllDriver($data,$join);

        $this->view->data['lastID'] = isset($driver_model->getLastDriver()->driver_id)?$driver_model->getLastDriver()->driver_id:0;
        
        $this->view->show('driver/index');
    }

    public function getdriver(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $steersman_model = $this->model->get('steersmanModel');
            
            if ($_POST['keyword'] == "*") {

                $list = $steersman_model->getAllSteersman();
            }
            else{
                $data = array(
                'where'=>'( steersman_name LIKE "%'.$_POST['keyword'].'%" )',
                );
                $list = $steersman_model->getAllSteersman($data);
            }
            
            foreach ($list as $rs) {
                // put in bold the written text
                $steersman_name = $rs->steersman_name;
                if ($_POST['keyword'] != "*") {
                    $steersman_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->steersman_name);
                }
                
                // add new option
                echo '<li onclick="set_item_driver(\''.$rs->steersman_id.'\',\''.$rs->steersman_name.'\',\''.$rs->steersman_code.'\',\''.$rs->steersman_cmnd.'\',\''.date('d-m-Y',$rs->steersman_birth).'\',\''.$rs->steersman_phone.'\',\''.$rs->steersman_bank.'\')">'.$steersman_name.'</li>';
            }
        }
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
            $driver = $this->model->get('driverModel');
            $data = array(
                        'driver_name' => trim($_POST['driver_name']),
                        'driver_phone' => trim($_POST['driver_phone']),
                        'driver_code' => trim($_POST['driver_code']),
                        'driver_bank' => trim($_POST['driver_bank']),
                        'driver_cmnd' => trim($_POST['driver_cmnd']),
                        'driver_birth' => strtotime(trim($_POST['driver_birth'])),
                        'vehicle' => trim($_POST['vehicle']),
                        'start_work' => strtotime(trim($_POST['start_work'])),
                        'end_work' => strtotime(trim($_POST['end_work'])),
                        'steersman' => trim($_POST['steersman']),
                        );
            if ($_POST['yes'] != "") {
                //$data['driver_update_user'] = $_SESSION['userid_logined'];
                //$data['driver_update_time'] = time();
                //var_dump($data);
                if ($driver->checkDriver($_POST['yes'],trim($_POST['driver_name']),trim($_POST['vehicle']))) {
                    echo "Thông tin này đã tồn tại";
                    return false;
                }
                else{
                    $driver->updateDriver($data,array('driver_id' => $_POST['yes']));
                    echo "Cập nhật thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|driver|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    }
            }
            else{
                //$data['driver_create_user'] = $_SESSION['userid_logined'];
                //$data['staff'] = $_POST['staff'];
                //var_dump($data);
                if ($driver->getDriverByWhere(array('driver_name'=>$data['driver_name'],'vehicle'=>$data['vehicle']))) {
                    echo "Thông tin này đã tồn tại";
                    return false;
                }
                else{
                    $driver->createDriver($data);
                    echo "Thêm thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$driver->getLastDriver()->driver_id."|driver|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
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
            $driver = $this->model->get('driverModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $driver->deleteDriver($data);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|driver|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                return true;
            }
            else{

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|driver|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

                return $driver->deleteDriver($_POST['data']);
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

            $driver = $this->model->get('driverModel');

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
                    if ($val[1] != null && $val[2] != null) {

                            if(!$driver->getDriverByWhere(array('driver_number'=>trim($val[1])))) {
                                $driver_data = array(
                                'driver_number' => trim($val[1]),
                                'driver_name' => trim($val[2]),
                                'driver_phone' => trim($val[3]),
                                );
                                $driver->createDriver($driver_data);
                            }
                            else if($driver->getDriverByWhere(array('driver_number'=>trim($val[1])))){
                                $id_driver = $driver->getDriverByWhere(array('driver_number'=>trim($val[1])))->driver_id;
                                $driver_data = array(
                                'driver_name' => trim($val[2]),
                                'driver_phone' => trim($val[3]),
                                );
                                $driver->updateDriver($driver_data,array('driver_id' => $id_driver));
                            }


                        
                    }
                    
                    //var_dump($this->getNameDistrict($this->lib->stripUnicode($val[1])));
                    // insert


                }
                //return $this->view->redirect('transport');
            
            return $this->view->redirect('driver');
        }
        $this->view->show('driver/import');

    }
    

    public function view() {
        
        $this->view->show('handling/view');
    }

}
?>