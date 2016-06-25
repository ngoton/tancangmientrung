<?php
Class revenueController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 6 ) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Doanh thu';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
            $xe = isset($_POST['xe']) ? $_POST['xe'] : null;
            
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'shipment_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 50;
            if(date('m') == 3)
                $batdau = '01-03-'.date('Y');
            else
                $batdau = '30-'.date('m-Y', strtotime("last month"));
            
            $ketthuc = date('d-m-Y'); //cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).'-'.date('m-Y');
            $xe = "";
            
        }

        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();

        $this->view->data['vehicles'] = $vehicles;

        $join = array('table'=>'customer, vehicle, road','where'=>'customer.customer_id = revenue.customer AND vehicle.vehicle_id = revenue.vehicle AND road_from = shipment_from AND road_to = shipment_to');

        $shipment_model = $this->model->get('revenueModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;

        $data = array(
            'where' => "1=1",
            );
        if($batdau != "" && $ketthuc != "" ){
            $data['where'] = $data['where'].' AND shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc);
        }
        if($xe != ""){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }

        $data['where'] = $data['where'].' AND way != 0';

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

        $this->view->data['limit'] = $limit;


        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => "1=1",
            );
        if($batdau != "" && $ketthuc != "" ){
            $data['where'] = $data['where'].' AND shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc);
        }
        if($xe != ""){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
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

        $data['where'] = $data['where'].' AND way != 0 AND shipment_ton > 0';
        
        $warehouse_model = $this->model->get('warehouseModel');
        
        
        

        $warehouse_data = array();
        
        $this->view->data['shipments'] = $shipment_model->getAllShipment($data,$join);

        $this->view->data['lastID'] = isset($shipment_model->getLastShipment()->shipment_id)?$shipment_model->getLastShipment()->revenue_id:0;

       

        foreach ($shipment_model->getAllShipment($data,$join) as $ship) {
            

            $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$ship->shipment_from.' OR warehouse_code = '.$ship->shipment_to.') AND start_time <= '.$ship->shipment_date.' AND end_time >= '.$ship->shipment_date));
        

            foreach ($warehouse as $warehouse) {
                
                    $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;
                    $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name;
                
                
            }
        }

        $this->view->data['warehouse'] = $warehouse_data;
        
        $this->view->show('revenue/index');
    }

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 6 ) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            $shipment = $this->model->get('revenueModel');
            //$ad = mktime(date("H"), date("i"), date("s"), date("m"), date("d")+1, date("Y")); 
            $tomorrow = strtotime(trim($_POST['shipment_date']));
            $data = array(
                        
                        'shipment_from' => trim($_POST['shipment_from']),
                        'shipment_to' => trim($_POST['shipment_to']),
                        'vehicle' => trim($_POST['vehicle']),
                        'customer' => trim($_POST['customer']),
                        'shipment_ton' => trim($_POST['shipment_ton']),
                        'shipment_charge' => trim(str_replace(',','',$_POST['shipment_charge'])),
                        
                        'shipment_date' => $tomorrow,
                        
                        );

            
                $data['shipment_revenue'] = $data['shipment_charge']*$data['shipment_ton'];
                
                


            $result = array();

            if ($_POST['yes'] != "") {
                //$data['shipment_update_user'] = $_SESSION['userid_logined'];
                //$data['shipment_update_time'] = time();
                //var_dump($data);
                

                if ($shipment->checkShipment($_POST['yes'],trim($_POST['shipment_from']),trim($_POST['shipment_to']),trim($_POST['vehicle']),$tomorrow,trim($_POST['customer']))) {
                    $result['err'] = "Bảng này đã tồn tại";
                    $result['revenue'] = 0;
                    $result['cost'] = 0;
                    $result['profit'] = 0;
                    echo json_encode($result);
                    return false;
                }
                else{

                     
                    

                    $shipment->updateShipment($data,array('revenue_id' => $_POST['yes']));
                    $result['err'] =  "Cập nhật thành công";
                    echo json_encode($result);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|revenue|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    }
            }
            else{
                //$data['shipment_create_user'] = $_SESSION['userid_logined'];
                //$data['staff'] = $_POST['staff'];
                //var_dump($data);
                
                if ($shipment->checkShipment(0,trim($_POST['shipment_from']),trim($_POST['shipment_to']),trim($_POST['vehicle']),$tomorrow,trim($_POST['customer']))) {
                    $result['err'] = "Bảng này đã tồn tại";
                    $result['revenue'] = 0;
                    $result['cost'] = 0;
                    $result['profit'] = 0;
                    echo json_encode($result);
                    return false;
                }
                else{
                    
                    $shipment->createShipment($data);
                    $result['err'] = "Thêm thành công";
                    
                    echo json_encode($result);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$shipment->getLastShipment()->revenue_id."|revenue|".implode("-",$data)."\n"."\r\n";
                        
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
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 6) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $shipment = $this->model->get('revenueModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $shipment->deleteShipment($data);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|revenue|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                return true;
            }
            else{

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|revenue|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

                return $shipment->deleteShipment($_POST['data']);
            }
            
        }
    }

    function export(){
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }

        $batdau = $this->registry->router->param_id;
        $ketthuc = $this->registry->router->page;
        $xe = $this->registry->router->order_by;
        
        $shipment_model = $this->model->get('revenueModel');
        $join = array('table'=>'customer, vehicle, road','where'=>'customer.customer_id = revenue.customer AND vehicle.vehicle_id = revenue.vehicle AND road_from = shipment_from AND road_to = shipment_to');

        $data = array(
            'where' => "1=1",
            );
        if($batdau != "" && $ketthuc != "" ){
            $data['where'] = $data['where'].' AND shipment_date >= '.$batdau.' AND shipment_date <= '.$ketthuc;
        }
        if($xe != ""){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }
        


        

        $data['where'] = $data['where'].' AND way != 0 AND shipment_ton > 0';

        $data['order_by'] = 'shipment_date';
        $data['order'] = 'ASC';
        
        $warehouse_model = $this->model->get('warehouseModel');
        
        
        

        $warehouse_data = array();
        
        
        
        $shipments = $shipment_model->getAllShipment($data,$join);

        

            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $objPHPExcel = new PHPExcel();

            

            $index_worksheet = 0; //(worksheet mặc định là 0, nếu tạo nhiều worksheet $index_worksheet += 1)
            $objPHPExcel->setActiveSheetIndex($index_worksheet)
               ->setCellValue('A1', 'STT')
               ->setCellValue('B1', 'Ngày')
               ->setCellValue('C1', 'Xe')
               ->setCellValue('D1', 'Khách hàng')
               ->setCellValue('E1', 'Nhận hàng')
               ->setCellValue('F1', 'Giao hàng')
               ->setCellValue('G1', 'Loại hàng')
               ->setCellValue('H1', 'Trọng lượng')
               ->setCellValue('I1', 'Đơn giá')
               ->setCellValue('J1', 'Thành tiền')
               ->setCellValue('K1', 'Thuế VAT')
               ->setCellValue('L1', 'Tổng tiền');
               

            

            
            
            

            if ($shipments) {

                $hang = 2;
                $i=1;

                $kho_data = array();
                foreach ($shipments as $row) {
                    
                    $warehouses = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$row->shipment_from.' OR warehouse_code = '.$row->shipment_to.') AND start_time <= '.$row->shipment_date.' AND end_time >= '.$row->shipment_date));
        

                    foreach ($warehouses as $warehouse) {
                        
                            $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;
                            $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name;
                        
                        
                    }


                    //$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$hang)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
                     $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $hang, $i++)
                        ->setCellValueExplicit('B' . $hang, $this->lib->hien_thi_ngay_thang($row->shipment_date))
                        ->setCellValue('C' . $hang, $row->vehicle_number)
                        ->setCellValue('D' . $hang, $row->customer_name)
                        ->setCellValue('E' . $hang, $warehouse_data['warehouse_name'][$row->shipment_from])
                        ->setCellValue('F' . $hang, $warehouse_data['warehouse_name'][$row->shipment_to])
                        ->setCellValue('G' . $hang, $row->customer_type)
                        ->setCellValue('H' . $hang, $row->shipment_ton)
                        ->setCellValue('I' . $hang, $row->shipment_charge)
                        ->setCellValue('J' . $hang, '=H'.$hang.'*I'.$hang)
                        ->setCellValue('K' . $hang, '=J'.$hang.'*10%')
                        ->setCellValue('L' . $hang, '=J'.$hang.'+K'.$hang);
                     $hang++;


                  }

          }

            $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

            $highestRow ++;


            $objPHPExcel->getActiveSheet()->getStyle('H2:L'.$highestRow)->getNumberFormat()->setFormatCode("#,##0_);[Black](#,##0)");
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(16);
            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

            // Set properties
            $objPHPExcel->getProperties()->setCreator("TCMT")
                            ->setLastModifiedBy($_SESSION['user_logined'])
                            ->setTitle("Sale Report")
                            ->setSubject("Sale Report")
                            ->setDescription("Sale Report.")
                            ->setKeywords("Sale Report")
                            ->setCategory("Sale Report");
            $objPHPExcel->getActiveSheet()->setTitle("Thong ke san luong");

            $objPHPExcel->getActiveSheet()->freezePane('A1');
            $objPHPExcel->setActiveSheetIndex(0);



            

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename= THỐNG KÊ SẢN LƯỢNG.xlsx");
            header("Cache-Control: max-age=0");
            ob_clean();
            $objWriter->save("php://output");
        
    }


    

}
?>