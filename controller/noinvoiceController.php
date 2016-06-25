<?php

Class noinvoiceController Extends baseController {

    public function index() {

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 6 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {

            return $this->view->redirect('user/login');

        }

        $this->view->data['lib'] = $this->lib;

        $this->view->data['title'] = 'Chi phí không hóa đơn';



        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;

            $order = isset($_POST['order']) ? $_POST['order'] : null;

            $page = isset($_POST['page']) ? $_POST['page'] : null;

            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;

            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;

            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;

            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;

            $xe = isset($_POST['xe']) ? $_POST['xe'] : null;

            $kh = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;

            $vong = isset($_POST['vong']) ? $_POST['vong'] : null;

            $trangthai = isset($_POST['staff']) ? $_POST['staff'] : null;

            

        }

        else{

            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'shipment_date ASC, shipment_id ';

            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';

            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;

            $keyword = "";

            $limit = 50;

            $batdau = '01-'.date('m-Y');

            $ketthuc = date('t-m-Y');

            $xe = 0;

            $kh = 0;

            $vong = (int)date('m',strtotime($batdau));

            $trangthai = date('Y',strtotime($batdau));

            

        }

        $ngayketthuc = date('d-m-Y', strtotime($ketthuc. ' + 1 days'));

        $vong = (int)date('m',strtotime($batdau));

        $trangthai = date('Y',strtotime($batdau));



        $vehicle_model = $this->model->get('vehicleModel');

        $vehicles = $vehicle_model->getAllVehicle();



        $this->view->data['vehicles'] = $vehicles;



        $customer_model = $this->model->get('customerModel');

        $customers = $customer_model->getAllCustomer();



        $this->view->data['customers'] = $customers;



        $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');



        $shipment_model = $this->model->get('shipmentModel');

        $sonews = $limit;

        $x = ($page-1) * $sonews;

        $pagination_stages = 2;



        $data = array(

            'where' => "shipment_ton > 0 AND (shipment_sub IS NULL OR shipment_sub != 1)",

            );

        if($batdau != "" && $ketthuc != "" ){

            $data['where'] = $data['where'].' AND shipment_date >= '.strtotime($batdau).' AND shipment_date < '.strtotime($ngayketthuc);

        }

        if($xe > 0){

            $data['where'] = $data['where'].' AND vehicle = '.$xe;

        }

        if($kh > 0){

            $data['where'] = $data['where'].' AND customer = '.$kh;

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

        $this->view->data['vong'] = $vong;

        $this->view->data['trangthai'] = $trangthai;



        $this->view->data['xe'] = $xe;

        $this->view->data['kh'] = $kh;



        $this->view->data['limit'] = $limit;





        $data = array(

            'order_by'=>$order_by,

            'order'=>$order,

            'limit'=>$x.','.$sonews,

            'where' => "shipment_ton > 0 AND (shipment_sub IS NULL OR shipment_sub != 1)",

            );

        if($batdau != "" && $ketthuc != "" ){

            $data['where'] = $data['where'].' AND shipment_date >= '.strtotime($batdau).' AND shipment_date < '.strtotime($ngayketthuc);

        }

        if($xe > 0){

            $data['where'] = $data['where'].' AND vehicle = '.$xe;

        }

        if($kh > 0){

            $data['where'] = $data['where'].' AND customer = '.$kh;

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

        

        $shipments = $shipment_model->getAllShipment($data,$join);



        



        
        $k=0;


        foreach ($shipments as $ship) {

            $qr = "SELECT * FROM vehicle_work WHERE vehicle = ".$ship->vehicle." AND start_work <= ".$ship->shipment_date." AND end_work >= ".$ship->shipment_date;
            if (!$shipment_model->queryShipment($qr)) {
                unset($shipments[$k]);
            }
            else{
                $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$ship->shipment_from.' AND road_to = '.$ship->shipment_to.' AND start_time <= '.$ship->shipment_date.' AND end_time >= '.$ship->shipment_date));

            

               $road_data['oil_add'][$ship->shipment_id] = ($ship->oil_add_dc == 5)?$ship->oil_add:0;

               $road_data['oil_add2'][$ship->shipment_id] = ($ship->oil_add_dc2 == 5)?$ship->oil_add2:0;



                $chek_rong = 0;

                

                foreach ($roads as $road) {

                    $road_data['bridge_cost'][$ship->shipment_id] = $road->bridge_cost;

                    $road_data['police_cost'][$ship->shipment_id] = $road->police_cost;

                    $road_data['tire_cost'][$ship->shipment_id] = $road->tire_cost;

                    $road_data['oil_cost'][$ship->shipment_id] = $road->road_oil;

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

                        if ($warehouse->warehouse_ton != 0 && $chek_rong==0){

                            $boiduong_tan += $trongluong * $warehouse->warehouse_ton;

                        }



                        $warehouse_data['warehouse_weight'][$warehouse->warehouse_code] = $warehouse->warehouse_weight;

                        $warehouse_data['warehouse_clean'][$warehouse->warehouse_code] = $warehouse->warehouse_clean;

                        $warehouse_data['warehouse_gate'][$warehouse->warehouse_code] = $warehouse->warehouse_gate;

                        $warehouse_data['warehouse_add'][$warehouse->warehouse_code] = $warehouse->warehouse_add;



                

                    

                }

                $warehouse_data['boiduong_cn'][$ship->shipment_id] = $boiduong_cont+$boiduong_tan;
            }

           $k++;

        }

        $this->view->data['shipments'] = $shipments;

        $this->view->data['warehouse'] = $warehouse_data;

        $this->view->data['road'] = $road_data;

        

        $this->view->show('noinvoice/index');

    }



    function export(){

        $this->view->disableLayout();

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }



        $batdau = $this->registry->router->param_id;

        $ketthuc = $this->registry->router->page;

        $xe = $this->registry->router->order_by;

        $kh = $this->registry->router->order;

        $ngayketthuc = strtotime(date('d-m-Y', strtotime(date('d-m-Y',$ketthuc). ' + 1 days')));

        $shipment_model = $this->model->get('shipmentModel');

        $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');



        $data = array(

            'where' => "shipment_ton > 0 AND (shipment_sub IS NULL OR shipment_sub != 1)",

            );

        if($batdau != "" && $ketthuc != "" ){

            $data['where'] = $data['where'].' AND shipment_date >= '.$batdau.' AND shipment_date < '.$ngayketthuc;

        }

        if($xe > 0){

            $data['where'] = $data['where'].' AND vehicle = '.$xe;

        }

        if($kh > 0){

            $data['where'] = $data['where'].' AND customer = '.$kh;

        }

        



        /*if ($_SESSION['role_logined'] == 3) {

            $data['where'] = $data['where'].' AND shipment_create_user = '.$_SESSION['userid_logined'];

            

        }*/



        





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

                ->setCellValue('A1', 'BẢNG KÊ CHI PHÍ KHÔNG HÓA ĐƠN')

                ->setCellValue('A3', 'STT')

               ->setCellValue('B3', 'Ngày')

               ->setCellValue('C3', 'Xe')

               ->setCellValue('D3', 'Khách hàng')

               ->setCellValue('E3', 'Nhận hàng')

               ->setCellValue('F3', 'Giao hàng')

               ->setCellValue('G3', 'Loại hàng')

               ->setCellValue('H3', 'Trọng lượng')

               ->setCellValue('I3', 'Đơn giá')

               ->setCellValue('J3', 'Doanh thu')

               ->setCellValue('K3', 'Bồi dưỡng')

               ->setCellValue('L3', 'Công an')

               ->setCellValue('M3', 'Vá vỏ rửa xe')

               ->setCellValue('N3', 'Cân xe')

               ->setCellValue('O3', 'Quét cont')

               ->setCellValue('P3', 'Vé cổng')

               ->setCellValue('Q3', 'Hoa hồng')

               ->setCellValue('R3', 'Thưởng vượt tải')

               ->setCellValue('S3', 'Chi phí phát sinh');

               



            



            

            

            



            if ($shipments) {



                $hang = 4;

                $i=1;



                $kho = array();

                $k=0;

                foreach ($shipments as $row) {
                    $qr = "SELECT * FROM vehicle_work WHERE vehicle = ".$row->vehicle." AND start_work <= ".$row->shipment_date." AND end_work >= ".$row->shipment_date;
                    if ($shipment_model->queryShipment($qr)) {
                        $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$row->shipment_from.' AND road_to = '.$row->shipment_to.' AND start_time <= '.$row->shipment_date.' AND end_time >= '.$row->shipment_date));

            

                       $road_data['oil_add'][$row->shipment_id] = ($row->oil_add_dc == 5)?$row->oil_add:0;

                       $road_data['oil_add2'][$row->shipment_id] = ($row->oil_add_dc2 == 5)?$row->oil_add2:0;



                        $chek_rong = 0;

                        

                        foreach ($roads as $road) {

                            $road_data['bridge_cost'][$row->shipment_id] = $road->bridge_cost;

                            $road_data['police_cost'][$row->shipment_id] = $road->police_cost;

                            $road_data['tire_cost'][$row->shipment_id] = $road->tire_cost;

                            $road_data['oil_cost'][$row->shipment_id] = $road->road_oil;

                            $road_data['way'][$row->shipment_id] = $road->way;

                            $chek_rong = ($road->way == 0)?1:0;



                        }



                        $warehouses = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$row->shipment_from.' OR warehouse_code = '.$row->shipment_to.') AND start_time <= '.$row->shipment_date.' AND end_time >= '.$row->shipment_date));

                    



                        $boiduong_cont = 0;

                        $boiduong_tan = 0;



                        

                        foreach ($warehouses as $warehouse) {

                            

                                $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;

                                $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name;



                                $tan = explode(".",$row->shipment_ton);

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

                                if ($warehouse->warehouse_ton != 0 && $chek_rong==0){

                                    $boiduong_tan += $trongluong * $warehouse->warehouse_ton;

                                }



                                $warehouse_data['warehouse_weight'][$warehouse->warehouse_code] = $warehouse->warehouse_weight;

                                $warehouse_data['warehouse_clean'][$warehouse->warehouse_code] = $warehouse->warehouse_clean;

                                $warehouse_data['warehouse_gate'][$warehouse->warehouse_code] = $warehouse->warehouse_gate;

                                $warehouse_data['warehouse_add'][$warehouse->warehouse_code] = $warehouse->warehouse_add;



                        

                            

                        }

                        $warehouse_data['boiduong_cn'][$row->shipment_id] = $boiduong_cont+$boiduong_tan;



                        $kho['boiduong_cn'][$row->shipment_id] = $warehouse_data['boiduong_cn'][$row->shipment_id];

                        $kho['warehouse_weight'][$row->shipment_from] = $warehouse_data['warehouse_weight'][$row->shipment_from];

                        $kho['warehouse_clean'][$row->shipment_from] = $warehouse_data['warehouse_clean'][$row->shipment_from];

                        $kho['warehouse_weight'][$row->shipment_to] = $warehouse_data['warehouse_weight'][$row->shipment_to];

                        $kho['warehouse_clean'][$row->shipment_to] = $warehouse_data['warehouse_clean'][$row->shipment_to];



                        $kho['warehouse_gate'][$row->shipment_to] = $warehouse_data['warehouse_gate'][$row->shipment_to];

                        $kho['warehouse_gate'][$row->shipment_from] = $warehouse_data['warehouse_gate'][$row->shipment_from];



                        if ($row->shipment_ton <= 0) {



                            $kho['boiduong_cn'][$row->shipment_id] = 0;

                            $kho['warehouse_weight'][$row->shipment_from] = 0;

                            $kho['warehouse_clean'][$row->shipment_from] = 0;



                            $kho['warehouse_weight'][$row->shipment_to] = 0;

                            $kho['warehouse_clean'][$row->shipment_to] = 0;



                            

                        }

                        if ($road_data['way'][$row->shipment_id] == 0) {



                            $kho['warehouse_weight'][$row->shipment_from] = 0;

                            $kho['warehouse_clean'][$row->shipment_from] = 0;



                            $kho['warehouse_weight'][$row->shipment_to] = 0;

                            $kho['warehouse_clean'][$row->shipment_to] = 0;



                            

                        }

                        





                            if ($kho['warehouse_gate'][$row->shipment_to] > 0 ) {

                                $kho['warehouse_gate'][$row->shipment_to] = $kho['warehouse_gate'][$row->shipment_to];

                                $kho['warehouse_gate'][$row->shipment_from] = 0;

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

                            ->setCellValue('K' . $hang, $kho['boiduong_cn'][$row->shipment_id])

                            ->setCellValue('L' . $hang, $road_data['police_cost'][$row->shipment_id])

                            ->setCellValue('M' . $hang, $road_data['tire_cost'][$row->shipment_id])

                            ->setCellValue('N' . $kho['warehouse_weight'][$row->shipment_from]+$kho['warehouse_weight'][$row->shipment_to])

                            ->setCellValue('O' . $kho['warehouse_clean'][$row->shipment_from]+$kho['warehouse_clean'][$row->shipment_to])

                            ->setCellValue('P' . $kho['warehouse_gate'][$row->shipment_to])

                            ->setCellValue('Q' . $row->commission*$row->commission_number)

                            ->setCellValue('R' . $row->shipment_bonus)

                            ->setCellValue('S' . ($row->approve==1?$row->cost_add:0));

                         $hang++;

                    }

                  }



          }



            $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();



            $highestRow ++;



            $objPHPExcel->getActiveSheet()->mergeCells('A1:S1');



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



            $objPHPExcel->getActiveSheet()->getStyle('H4:S'.$highestRow)->getNumberFormat()->setFormatCode("#,##0_);[Black](#,##0)");

            $objPHPExcel->getActiveSheet()->getStyle('A3:S3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A3:S3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A3:S3')->getFont()->setBold(true);

            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);

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

            $objPHPExcel->getActiveSheet()->setTitle("Thong ke chi phi k HĐ");



            $objPHPExcel->getActiveSheet()->freezePane('A4');

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