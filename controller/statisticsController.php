<?php
Class statisticsController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 4 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Thống kê';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
            $xe = isset($_POST['xe']) ? $_POST['xe'] : null;
            $loc = isset($_POST['vong']) ? $_POST['vong'] : null;
            $vong = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;
            $trangthai = isset($_POST['staff']) ? $_POST['staff'] : null;
            
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'shipment_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 50;
            $batdau = '01-'.date('m-Y');
            $ketthuc = date('t-m-Y'); //cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).'-'.date('m-Y');
            $xe = 0;
            $loc = 0;
            $vong = (int)date('m',strtotime($batdau));
            $trangthai = date('Y',strtotime($batdau));
            
        }
        $vong = (int)date('m',strtotime($batdau));
        $trangthai = date('Y',strtotime($batdau));

        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();

        $this->view->data['vehicles'] = $vehicles;

        $join = array('table'=>'customer, vehicle, road','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle AND road_from = shipment_from AND road_to = shipment_to AND shipment_date >= start_time AND shipment_date <= end_time');

        $shipment_model = $this->model->get('shipmentModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;

        $data = array(
            'where' => "1=1",
            );
        if($batdau != "" && $ketthuc != "" ){
            $data['where'] = $data['where'].' AND shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc);
        }
        if($xe != 0){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }
        if ($loc != 0) {
            $data['where'] .= ' AND (oil_add_dc = '.$loc.' OR oil_add_dc2 = '.$loc.')';
        }

        /*if ($_SESSION['role_logined'] == 3) {
            $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];
            
        }*/
        //$data['where'] = $data['where'].' AND way != 0';

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
        $this->view->data['loc'] = $loc;
        $this->view->data['vong'] = $vong;
        $this->view->data['trangthai'] = $trangthai;

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
        if($xe != 0){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }
        if ($loc != 0) {
            $data['where'] .= ' AND (oil_add_dc = '.$loc.' OR oil_add_dc2 = '.$loc.')';
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

        //$data['where'] = $data['where'].' AND way != 0';
        
        $warehouse_model = $this->model->get('warehouseModel');
        $road_model = $this->model->get('roadModel');
        
        

        $warehouse_data = array();
        $road_data = array();
        
        $shipments = $shipment_model->getAllShipment($data,$join);

        $this->view->data['shipments'] = $shipments;

        

        foreach ($shipments as $ship) {
            $check_sub = 1;
           if ($ship->shipment_sub==1) {
               $check_sub = 0;
           }

           $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$ship->shipment_from.' AND road_to = '.$ship->shipment_to.' AND start_time <= '.$ship->shipment_date.' AND end_time >= '.$ship->shipment_date));
            

            $chek_rong = 0;
            
            foreach ($roads as $road) {
                $road_data['bridge_cost'][$ship->shipment_id] = $road->bridge_cost*$check_sub;
                $road_data['police_cost'][$ship->shipment_id] = $road->police_cost*$check_sub;
                $road_data['tire_cost'][$ship->shipment_id] = $road->tire_cost*$check_sub;
                $road_data['oil_cost'][$ship->shipment_id] = $road->road_oil*$check_sub;
                $road_data['way'][$ship->shipment_id] = $road->way;
                $chek_rong = ($road->way == 0)?1:0;

            }

            $warehouses = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$ship->shipment_from.' OR warehouse_code = '.$ship->shipment_to.') AND start_time <= '.$ship->shipment_date.' AND end_time >= '.$ship->shipment_date));
        

            $boiduong_cont = 0;
            $boiduong_tan = 0;

            
            foreach ($warehouses as $warehouse) {
                
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


                    if ($warehouse->warehouse_add != 0) {
                        $boiduong_cont += $warehouse->warehouse_add;
                    }
                    if ($warehouse->warehouse_ton != 0){
                        $boiduong_tan += $trongluong * $warehouse->warehouse_ton;
                    }

                    $warehouse_data['warehouse_weight'][$warehouse->warehouse_code] = $warehouse->warehouse_weight;
                    $warehouse_data['warehouse_clean'][$warehouse->warehouse_code] = $warehouse->warehouse_clean;
                    $warehouse_data['warehouse_gate'][$warehouse->warehouse_code] = $warehouse->warehouse_gate;
                    $warehouse_data['warehouse_add'][$warehouse->warehouse_code] = $warehouse->warehouse_add;

            
                
            }
            $warehouse_data['boiduong_cn'][$ship->shipment_id] = ($boiduong_cont+$boiduong_tan)*$check_sub;
        }

        $this->view->data['warehouse'] = $warehouse_data;
        $this->view->data['road'] = $road_data;
        
        $this->view->show('statistics/index');
    }

    function export(){
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }

        $batdau = $this->registry->router->param_id;
        $ketthuc = $this->registry->router->page;
        $xe = $this->registry->router->order_by;
        $diadiem = $this->registry->router->order;
        
        $shipment_model = $this->model->get('shipmentModel');
        $join = array('table'=>'customer, vehicle, road','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle AND road_from = shipment_from AND road_to = shipment_to AND shipment_date >= start_time AND shipment_date <= end_time');

        $data = array(
            'where' => "1=1",
            );
        if($batdau != "" && $ketthuc != "" ){
            $data['where'] = $data['where'].' AND shipment_date >= '.$batdau.' AND shipment_date <= '.$ketthuc;
        }
        if($xe > 0){
            $data['where'] = $data['where'].' AND vehicle = '.$xe;
        }
        if($diadiem > 0){
            $data['where'] = ' AND (oil_add_dc = '.$diadiem.' OR oil_add_dc2 = '.$diadiem.')';
        }
        
        


        $data['order_by'] = 'shipment_date';
        $data['order'] = 'ASC';
        
        $warehouse_model = $this->model->get('warehouseModel');
        $road_model = $this->model->get('roadModel');
        
        

        $warehouse_data = array();
        $road_data = array();
        
        
        
        $shipments = $shipment_model->getAllShipment($data,$join);

        

            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $objPHPExcel = new PHPExcel();

            

            $index_worksheet = 0; //(worksheet mặc định là 0, nếu tạo nhiều worksheet $index_worksheet += 1)
            $objPHPExcel->setActiveSheetIndex($index_worksheet)
                ->setCellValue('A1', 'BẢNG KÊ')
                ->setCellValue('A3', 'STT')
               ->setCellValue('B3', 'Ngày')
               ->setCellValue('C3', 'Xe')
               ->setCellValue('D3', 'Kho đi')
               ->setCellValue('E3', 'Kho đến')
               ->setCellValue('F3', 'Doanh thu')
               ->setCellValue('G3', 'Chi phí')
               ->setCellValue('G4', 'Công an')
               ->setCellValue('H4', 'Cầu đường')
               ->setCellValue('I4', 'Dầu')
               ->setCellValue('J4', 'Địa điểm');
               


            if ($shipments) {

                $hang = 5;
                $i=1;

                $kho_data = array();
                foreach ($shipments as $row) {
                    $check_sub = 1;
                   if ($row->shipment_sub==1) {
                       $check_sub = 0;
                   }

                    $dc = '';

                    if ($row->oil_add_dc==1 && $row->oil_add>0) {
                        $dc = "Bãi";
                    }
                    else if ($row->oil_add_dc==2 && $row->oil_add>0) {
                        $dc = "Long Bình";
                    }
                    else if ($row->oil_add_dc==3 && $row->oil_add>0) {
                        $dc = "Đak Lak";
                    }
                    else if ($row->oil_add_dc==4 && $row->oil_add>0) {
                        $dc = "Quy Nhơn";
                    }
                    else if ($row->oil_add_dc==6 && $row->oil_add>0) {
                        $dc = "Quỳnh Trung";
                    }
                    else if ($row->oil_add_dc==7 && $row->oil_add>0) {
                        $dc = "GL-78-Chuprong";
                    }
                    else if ($row->oil_add_dc==8 && $row->oil_add>0) {
                        $dc = "BTM-Tín Nghĩa";
                    }
                    else if ($row->oil_add_dc==5 && $row->oil_add>0) {
                        $dc = "Dọc đường";
                    }

                    $dc2 = '';

                    if ($row->oil_add_dc2==1 && $row->oil_add2>0) {
                        $dc2 = "Bãi";
                    }
                    else if ($row->oil_add_dc2==2 && $row->oil_add2>0) {
                        $dc2 = "Long Bình";
                    }
                    else if ($row->oil_add_dc2==3 && $row->oil_add2>0) {
                        $dc2 = "Đak Lak";
                    }
                    else if ($row->oil_add_dc2==4 && $row->oil_add2>0) {
                        $dc2 = "Quy Nhơn";
                    }
                    else if ($row->oil_add_dc2==6 && $row->oil_add2>0) {
                        $dc2 = "Quỳnh Trung";
                    }
                    else if ($row->oil_add_dc2==7 && $row->oil_add2>0) {
                        $dc2 = "GL-78-Chuprong";
                    }
                    else if ($row->oil_add_dc2==8 && $row->oil_add2>0) {
                        $dc2 = "ĐL-Đức Thành";
                    }
                    else if ($row->oil_add_dc2==5 && $row->oil_add2>0) {
                        $dc2 = "Dọc đường";
                    }

                    
                    $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$row->shipment_from.' AND road_to = '.$row->shipment_to.' AND start_time <= '.$row->shipment_date.' AND end_time >= '.$row->shipment_date));
            

                    $chek_rong = 0;
                    
                    foreach ($roads as $road) {
                        $road_data['bridge_cost'][$row->shipment_id] = $road->bridge_cost*$check_sub;
                        $road_data['police_cost'][$row->shipment_id] = $road->police_cost*$check_sub;
                        $road_data['tire_cost'][$row->shipment_id] = $road->tire_cost*$check_sub;
                        $road_data['oil_cost'][$row->shipment_id] = $road->road_oil*$check_sub;
                        $road_data['way'][$row->shipment_id] = $road->way;
                        $chek_rong = ($road->way == 0)?1:0;

                    }

                    $warehouses = $warehouse_model->getAllWarehouse(array('where'=>'warehouse_id = '.$row->shipment_from.' OR warehouse_id = '.$row->shipment_to));
        

                    foreach ($warehouses as $warehouse) {
                        
                            $warehouse_data['warehouse_id'][$warehouse->warehouse_id] = $warehouse->warehouse_id;
                            $warehouse_data['warehouse_name'][$warehouse->warehouse_id] = $warehouse->warehouse_name;
                        
                        
                    }


                    //$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$hang)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
                     $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $hang, $i++)
                        ->setCellValueExplicit('B' . $hang, $this->lib->hien_thi_ngay_thang($row->shipment_date))
                        ->setCellValue('C' . $hang, $row->vehicle_number)
                        ->setCellValue('D' . $hang, $warehouse_data['warehouse_name'][$row->shipment_from])
                        ->setCellValue('E' . $hang, $warehouse_data['warehouse_name'][$row->shipment_to])
                        ->setCellValue('F' . $hang, $row->shipment_revenue)
                        ->setCellValue('G' . $hang, $road_data['police_cost'][$row->shipment_id])
                        ->setCellValue('H' . $hang, (($road_data['bridge_cost'][$row->shipment_id]*1.1)+$row->bridge_cost_add) )
                        ->setCellValue('I' . $hang, (($row->oil_add*round($row->oil_cost*1.1))+($row->oil_add2*round($row->oil_cost*1.1))))
                        ->setCellValue('J' . $hang, $dc.' '.$dc2);
                     $hang++;


                  }


          }

          

            $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

            $highestRow ++;

            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('E'.$highestRow, 'Tổng chuyến: '.($i-1))
                        ->setCellValue('F'.$highestRow, '=SUM(F5:F'.($highestRow-1).')')
                        ->setCellValue('G'.$highestRow, '=SUM(G5:G'.($highestRow-1).')')
                        ->setCellValue('H'.$highestRow, '=SUM(H5:H'.($highestRow-1).')')
                        ->setCellValue('I'.$highestRow, '=SUM(I5:I'.($highestRow-1).')');


            $objPHPExcel->getActiveSheet()->mergeCells('A1:L1');

            $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
            $objPHPExcel->getActiveSheet()->mergeCells('B3:B4');
            $objPHPExcel->getActiveSheet()->mergeCells('C3:C4');
            $objPHPExcel->getActiveSheet()->mergeCells('D3:D4');
            $objPHPExcel->getActiveSheet()->mergeCells('E3:E4');
            $objPHPExcel->getActiveSheet()->mergeCells('F3:F4');
            $objPHPExcel->getActiveSheet()->mergeCells('G3:J3');

            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setSize(18);

            $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
                array(
                    
                    'font' => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A'.$highestRow.':J'.$highestRow)->applyFromArray(
                array(
                    
                    'font' => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('F5:J'.$highestRow)->getNumberFormat()->setFormatCode("#,##0_);[Black](#,##0)");
            $objPHPExcel->getActiveSheet()->getStyle('A3:J4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A3:J4')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A3:J4')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);
            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);

            // Set properties
            $objPHPExcel->getProperties()->setCreator("TCMT")
                            ->setLastModifiedBy($_SESSION['user_logined'])
                            ->setTitle("Sale Report")
                            ->setSubject("Sale Report")
                            ->setDescription("Sale Report.")
                            ->setKeywords("Sale Report")
                            ->setCategory("Sale Report");
            $objPHPExcel->getActiveSheet()->setTitle("Thong ke");

            $objPHPExcel->getActiveSheet()->freezePane('A5');
            $objPHPExcel->setActiveSheetIndex(0);



            

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename= THỐNG KÊ.xlsx");
            header("Cache-Control: max-age=0");
            ob_clean();
            $objWriter->save("php://output");
        
    }

}
?>