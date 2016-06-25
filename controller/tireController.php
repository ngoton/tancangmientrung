<?php
Class tireController Extends baseController {
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
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'tire_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
        }

        

        $tire_model = $this->model->get('tireModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $tongsodong = count($tire_model->getAllTire());
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
            $search = '( tire_serie LIKE "%'.$keyword.'%" )';
            $data['where'] = $search;
        }
        
        
        
        $this->view->data['tires'] = $tire_model->getAllTire($data);

        $this->view->data['lastID'] = isset($tire_model->getLastTire()->tire_id)?$tire_model->getLastTire()->tire_id:0;
        
        $this->view->show('tire/index');
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
            $tire = $this->model->get('tireModel');
            $data = array(
                        'tire_serie' => trim($_POST['tire_serie']),
                        'tire_position' => trim($_POST['tire_position']),
                        'tire_cost' => trim(str_replace(',','',$_POST['tire_cost'])),
                        'tire_status' => trim($_POST['tire_status']),
                        'tire_name' => trim($_POST['tire_name']),
                        );
            if ($_POST['yes'] != "") {
                //$data['tire_update_user'] = $_SESSION['userid_logined'];
                //$data['tire_update_time'] = time();
                //var_dump($data);
                if ($tire->checkTire($_POST['yes'],trim($_POST['tire_serie']))) {
                    echo "Vỏ này đã tồn tại";
                    return false;
                }
                else{
                    $tire->updateTire($data,array('tire_id' => $_POST['yes']));
                    echo "Cập nhật thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|tire|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    }
            }
            else{
                //$data['tire_create_user'] = $_SESSION['userid_logined'];
                //$data['staff'] = $_POST['staff'];
                //var_dump($data);
                if ($tire->getTireByWhere(array('tire_serie'=>$_POST['tire_serie']))) {
                    echo "Vỏ này đã tồn tại";
                    return false;
                }
                else{
                    $tire->createTire($data);
                    echo "Thêm thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$tire->getLastTire()->tire_id."|tire|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
                    
        }
    }

    public function multi(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 5) {
            return $this->view->redirect('user/login');
        }
        
            $tire = $this->model->get('tireModel');
        for($i=1; $i<=218; $i++){
            if ($i<10) {
                $ma = '00'.$i;
            }
            elseif ($i>=10 && $i<100) {
                $ma = '0'.$i;
            }
            else{
                $ma = $i;
            }
            
            $data = array(
                        'tire_serie' => '3000000000'.$ma,
                        'tire_position' => 2,
                        'tire_cost' => 5000000,
                        'tire_status' => 2,
                        'tire_name' => 'DOUBLEROAD',
                        );
            
            
                
                    $tire->createTire($data);
                    echo "Thêm thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$tire->getLastTire()->tire_id."|tire|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
        }   
    }

    public function report() {
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
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'tire_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }

        

        $tire_model = $this->model->get('tireModel');
        
        
        $tires = $tire_model->queryTire('SELECT tire_name, COUNT(tire_status) as tire_sd FROM tire WHERE tire_status=1 GROUP BY tire_name');
        $tire_data = array();
        foreach ($tires as $tire) {
            $tire_data[$tire->tire_name]['tire_sd'] = $tire->tire_sd;
        }

        $tires = $tire_model->queryTire('SELECT tire_name, COUNT(tire_status) as tire_hu FROM tire WHERE tire_status=0 GROUP BY tire_name');
        
        foreach ($tires as $tire) {
            $tire_data[$tire->tire_name]['tire_hu'] = $tire->tire_hu;
        }

        $tires = $tire_model->queryTire('SELECT tire_name, COUNT(tire_status) as tire_duphong FROM tire WHERE tire_status=2 GROUP BY tire_name');
        
        foreach ($tires as $tire) {
            $tire_data[$tire->tire_name]['tire_duphong'] = $tire->tire_duphong;
        }

        $tires = $tire_model->queryTire('SELECT tire_name, COUNT(tire_status) as tire_ban FROM tire WHERE tire_status=3 GROUP BY tire_name');
        
        foreach ($tires as $tire) {
            $tire_data[$tire->tire_name]['tire_ban'] = $tire->tire_ban;
        }
        
        $this->view->data['tires_data'] = $tire_data;

        $tires = $tire_model->queryTire('SELECT tire_name FROM tire GROUP BY tire_name');
        $this->view->data['tires'] = $tires;
        
        
        $this->view->show('tire/report');
    }

    public function statistics() {
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
            $tire = isset($_POST['tire']) ? $_POST['tire'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'tire_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            $tire = "";
        }

        

        $tire_model = $this->model->get('tireModel');
        $shipment_model = $this->model->get('shipmentModel');
        $equipment_model = $this->model->get('equipmentModel');
        
        $data = array('where'=>'1=1 GROUP BY tire_name');


        $tires = $tire_model->getAllTire($data);
        
        $this->view->data['tires'] = $tires;

        $ser =array();

        foreach ($tires as $t) {
            $ts = $tire_model->getAllTire(array('where'=>'(tire_id in (SELECT tire_in FROM equipment WHERE approve = 1) OR tire_id in (SELECT tire_out FROM equipment )) AND tire_name = "'.$t->tire_name.'"'));

            $ser[$t->tire_name] = count($ts);
        }

       $this->view->data['tongvo'] = $ser;

        $equipment_in = array();
        $equipment_out = array();
        $km = array();
        $doanhthu = array();

        $equipments = $equipment_model->getAllEquipment(array('where'=>'approve = 1'));

        foreach ($equipments as $e) {
            $equipment_in[$e->tire_in][$e->vehicle]['met'] = $e->vehicle_km;
            $equipment_in[$e->tire_in][$e->vehicle]['date'] = $e->equipment_date;

            $equipment_out[$e->tire_out][$e->vehicle]['met'] = $e->vehicle_km;
            $equipment_out[$e->tire_out][$e->vehicle]['date'] = $e->equipment_date;

            $t_data = array(
                'where' => 'tire_id = '.$e->tire_out,
            );
            $tires = $tire_model->getAllTire($t_data);
            

            foreach ($tires as $tire) {
                if (!isset($equipment_in[$tire->tire_id][$e->vehicle]['met'])) {
                    $km[$tire->tire_name] = isset($km[$tire->tire_name])?($km[$tire->tire_name]+$equipment_out[$tire->tire_id][$e->vehicle]['met']):(0+$equipment_out[$tire->tire_id][$e->vehicle]['met']);
                }
                else if(isset($equipment_in[$tire->tire_id][$e->vehicle]['met'])){
                    $km[$tire->tire_name] = isset($km[$tire->tire_name])?($km[$tire->tire_name]+($equipment_out[$tire->tire_id][$e->vehicle]['met'] - $equipment_in[$tire->tire_id][$e->vehicle]['met'])):($equipment_out[$tire->tire_id][$e->vehicle]['met'] - $equipment_in[$tire->tire_id][$e->vehicle]['met']);
                }

                if (!isset($equipment_in[$tire->tire_id][$e->vehicle]['date'])) {
                    //$equipment_data[$tire->tire_id]['date'] = $equipment_out[$tire->tire_id][$e->vehicle]['date'];
                }
                else if(isset($equipment_in[$tire->tire_id][$e->vehicle]['date'])){
                    $date = $equipment_in[$tire->tire_id][$e->vehicle]['date'] ;
                    $end_date = $equipment_out[$tire->tire_id][$e->vehicle]['date'] ;

                    while ($date <= $end_date) {
                     $ship_data = array(
                        'where' => 'shipment_date = '.$date.' AND vehicle = '.$e->vehicle,
                    );

                     $shipments = $shipment_model->getAllShipment($ship_data);


                   foreach ($shipments as $shipment) {
                       $doanhthu[$tire->tire_name] = isset($doanhthu[$tire->tire_name])?($doanhthu[$tire->tire_name]+$shipment->shipment_revenue):(0+$shipment->shipment_revenue);
                   }

                     $date = strtotime(date ("d-m-Y", strtotime("+1 day", $date)));
                     }
                }
                
            }

        }


        $this->view->data['km'] = $km;
        $this->view->data['doanhthu'] = $doanhthu;
        
        $this->view->show('tire/statistics');
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
            $tire = $this->model->get('tireModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $tire->deleteTire($data);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|tire|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                return true;
            }
            else{

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|tire|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

                return $tire->deleteTire($_POST['data']);
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

                        if (trim($val[2]) == "Vỏ đầu kéo") {
                            $vt = 1;
                        }
                        else if (trim($val[2]) == "Vỏ móoc xe") {
                            $vt = 2;
                        }
                        if (trim($val[4]) == "Đang sử dụng") {
                            $tt = 1;
                        }
                        else if (trim($val[4]) == "Hư") {
                            $tt = 0;
                        }
                        else if (trim($val[4]) == "Dự phòng") {
                            $tt = 2;
                        }
                        else if (trim($val[4]) == "Đã bán") {
                            $tt = 3;
                        }
                            if(!$tire->getTireByWhere(array('tire_serie'=>trim($val[1])))) {
                                $tire_data = array(
                                'tire_serie' => trim($val[1]),
                                'tire_position' => $vt,
                                'tire_cost' => trim($val[3]),
                                'tire_status' => $tt,
                                'tire_name' => trim($val[5]),
                                );
                                $tire->createTire($tire_data);
                            }
                            else if($tire->getTireByWhere(array('tire_serie'=>trim($val[1])))){
                                $id_tire = $tire->getTireByWhere(array('tire_serie'=>trim($val[1])))->tire_id;
                                $tire_data = array(
                                'tire_position' => $vt,
                                'tire_cost' => trim($val[3]),
                                'tire_status' => $tt,
                                'tire_name' => trim($val[5]),
                                );
                                $tire->updateTire($tire_data,array('tire_id' => $id_tire));
                            }


                        
                    }
                    
                    //var_dump($this->getNameDistrict($this->lib->stripUnicode($val[1])));
                    // insert


                }
                //return $this->view->redirect('transport');
            
            return $this->view->redirect('tire');
        }
        $this->view->show('tire/import');

    }

    public function view() {
        
        $this->view->show('tire/view');
    }

}
?>