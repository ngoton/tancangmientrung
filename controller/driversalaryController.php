<?php
Class driversalaryController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Bảng lương tài xế';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
        }
        else{
            if(date('m') == 3)
                $batdau = '01-03-'.date('Y');
            else
                $batdau = '30-'.date('m-Y', strtotime("last month"));
            
            $ketthuc = date('d-m-Y'); //cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).'-'.date('m-Y');
        }

        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();
        foreach ($vehicles as $vehicle) {
            $vehicle_data[$vehicle->vehicle_id] = $vehicle->vehicle_number;
        }
        $this->view->data['vehicle_data'] = $vehicle_data;

        $d_data = array(
            'where'=> 'end_work > '.strtotime($batdau),
        );
        $driver_model = $this->model->get('driverModel');
        $drivers = $driver_model->getAllDriver($d_data);

        $shipment_model = $this->model->get('shipmentModel');
        $warehouse_model = $this->model->get('warehouseModel');
        $road_model = $this->model->get('roadModel');

        foreach ($drivers as $driver) {
               
            $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');
            $shipments = $shipment_model->getAllShipment(array('where'=>'vehicle = '.$driver->vehicle.' AND shipment_date >= '.strtotime($batdau).' AND shipment_date <= '.strtotime($ketthuc)),$join);

            $doanhthu = array();
            $chiphiphatsinh = array();

            $warehouse_data = array();
            $road_data = array();


            foreach ($shipments as $shipment) {
                $check_sub = 1;
               if ($shipment->shipment_sub==1) {
                   $check_sub = 0;
               }
                
                if($driver->end_work > $shipment->shipment_date && $shipment->shipment_date >= $driver->start_work){
                    if ($shipment->sub_driver == 1) {
                        $doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle] = isset($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle])?($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other) : (0+$shipment->shipment_revenue+$shipment->revenue_other);
                    }
                    if ($shipment->sub_driver2 == 1) {
                        $doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle] = isset($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle])?($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other) : (0+$shipment->shipment_revenue+$shipment->revenue_other);
                    }

                    $doanhthu[$driver->driver_id][$shipment->vehicle] = isset($doanhthu[$driver->driver_id][$shipment->vehicle])?($doanhthu[$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other) : (0+$shipment->shipment_revenue+$shipment->revenue_other);
                    $chiphiphatsinh[$driver->driver_id][$shipment->vehicle] = isset($chiphiphatsinh[$driver->driver_id][$shipment->vehicle])?($chiphiphatsinh[$driver->driver_id][$shipment->vehicle]+(($shipment->approve==1)?$shipment->cost_add:0)) : (0+(($shipment->approve==1)?$shipment->cost_add:0));

                    $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$shipment->shipment_from.' AND road_to = '.$shipment->shipment_to.' AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));
                    
                   
                    $check_rong = 0;
                    
                    foreach ($roads as $road) {
                        $road_data['bridge_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['bridge_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['bridge_cost'][$driver->driver_id][$shipment->vehicle]+$road->bridge_cost*$check_sub):0+$road->bridge_cost*$check_sub;
                        $road_data['police_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['police_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['police_cost'][$driver->driver_id][$shipment->vehicle]+$road->police_cost*$check_sub):0+$road->police_cost*$check_sub;
                        $road_data['oil_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['oil_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['oil_cost'][$driver->driver_id][$shipment->vehicle]+($road->road_oil*$shipment->oil_cost)*$check_sub):0+($road->road_oil*$shipment->oil_cost)*$check_sub;
                        $road_data['road_time'][$driver->driver_id][$shipment->vehicle] = isset($road_data['road_time'][$driver->driver_id][$shipment->vehicle])?($road_data['road_time'][$driver->driver_id][$shipment->vehicle]+$road->road_time*$check_sub):0+$road->road_time*$check_sub;
                        $road_data['tire_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['tire_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['tire_cost'][$driver->driver_id][$shipment->vehicle]+$road->tire_cost*$check_sub):0+$road->tire_cost*$check_sub;
                        if ($shipment->sub_driver == 1) {
                            $road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle]+$road->police_cost*$check_sub):0+$road->police_cost*$check_sub;
                            $road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle] = isset($road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle])?($road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle]+$road->road_time*$check_sub):0+$road->road_time*$check_sub;
                        }

                        if ($shipment->sub_driver2 == 1) {
                            $road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle]+$road->police_cost*$check_sub):0+$road->police_cost*$check_sub;
                            $road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle] = isset($road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle])?($road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle]+$road->road_time*$check_sub):0+$road->road_time*$check_sub;
                        }

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
                    $warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;
                    
                    if ($shipment->sub_driver == 1) {
                        $warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                    }

                    if ($shipment->sub_driver2 == 1) {
                        $warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                    }
                }
                
                
            }
        }

        $this->view->data['drivers'] = $drivers;
        $this->view->data['warehouse'] = $warehouse_data;
        $this->view->data['road'] = $road_data;

        $this->view->data['doanhthu'] = $doanhthu;
        $this->view->data['chiphiphatsinh'] = $chiphiphatsinh;

        $this->view->data['batdau'] = $batdau;
        $this->view->data['ketthuc'] = $ketthuc;
        $this->view->show('driversalary/index');
    }



   function export(){
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }

        $batdau = $this->registry->router->param_id;
        $ketthuc = $this->registry->router->page;
        
        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();
        foreach ($vehicles as $vehicle) {
            $vehicle_data[$vehicle->vehicle_id] = $vehicle->vehicle_number;
        }

        $d_data = array(
            'where'=> 'end_work > '.$batdau,
        );
        $driver_model = $this->model->get('driverModel');
        $drivers = $driver_model->getAllDriver($d_data);

        $shipment_model = $this->model->get('shipmentModel');
        $warehouse_model = $this->model->get('warehouseModel');
        $road_model = $this->model->get('roadModel');

        foreach ($drivers as $driver) {
               
            $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');
            $shipments = $shipment_model->getAllShipment(array('where'=>'vehicle = '.$driver->vehicle.' AND shipment_date >= '.$batdau.' AND shipment_date <= '.$ketthuc),$join);

            $doanhthu = array();
            $chiphiphatsinh = array();

            $warehouse_data = array();
            $road_data = array();


            foreach ($shipments as $shipment) {
                $check_sub = 1;
               if ($shipment->shipment_sub==1) {
                   $check_sub = 0;
               }

                if($driver->end_work > $shipment->shipment_date && $shipment->shipment_date >= $driver->start_work){
                    if ($shipment->sub_driver == 1) {
                        $doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle] = isset($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle])?($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other) : (0+$shipment->shipment_revenue+$shipment->revenue_other);
                    }
                    if ($shipment->sub_driver2 == 1) {
                        $doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle] = isset($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle])?($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other) : (0+$shipment->shipment_revenue+$shipment->revenue_other);
                    }

                    $doanhthu[$driver->driver_id][$shipment->vehicle] = isset($doanhthu[$driver->driver_id][$shipment->vehicle])?($doanhthu[$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other) : (0+$shipment->shipment_revenue+$shipment->revenue_other);
                    $chiphiphatsinh[$driver->driver_id][$shipment->vehicle] = isset($chiphiphatsinh[$driver->driver_id][$shipment->vehicle])?($chiphiphatsinh[$driver->driver_id][$shipment->vehicle]+(($shipment->approve==1)?$shipment->cost_add:0)) : (0+(($shipment->approve==1)?$shipment->cost_add:0));

                    $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$shipment->shipment_from.' AND road_to = '.$shipment->shipment_to.' AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));
                    
                   
                    $check_rong = 0;
                    
                    foreach ($roads as $road) {
                        $road_data['bridge_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['bridge_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['bridge_cost'][$driver->driver_id][$shipment->vehicle]+$road->bridge_cost*$check_sub):0+$road->bridge_cost*$check_sub;
                        $road_data['police_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['police_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['police_cost'][$driver->driver_id][$shipment->vehicle]+$road->police_cost*$check_sub):0+$road->police_cost*$check_sub;
                        $road_data['oil_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['oil_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['oil_cost'][$driver->driver_id][$shipment->vehicle]+($road->road_oil*$shipment->oil_cost)*$check_sub):0+($road->road_oil*$shipment->oil_cost)*$check_sub;
                        $road_data['road_time'][$driver->driver_id][$shipment->vehicle] = isset($road_data['road_time'][$driver->driver_id][$shipment->vehicle])?($road_data['road_time'][$driver->driver_id][$shipment->vehicle]+$road->road_time*$check_sub):0+$road->road_time*$check_sub;
                        $road_data['tire_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['tire_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['tire_cost'][$driver->driver_id][$shipment->vehicle]+$road->tire_cost*$check_sub):0+$road->tire_cost*$check_sub;
                        if ($shipment->sub_driver == 1) {
                            $road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle]+$road->police_cost*$check_sub):0+$road->police_cost*$check_sub;
                            $road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle] = isset($road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle])?($road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle]+$road->road_time*$check_sub):0+$road->road_time*$check_sub;
                        }

                        if ($shipment->sub_driver2 == 1) {
                            $road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle] = isset($road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle])?($road_data['sub_driver']['police_cost'][$driver->driver_id][$shipment->vehicle]+$road->police_cost*$check_sub):0+$road->police_cost*$check_sub;
                            $road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle] = isset($road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle])?($road_data['sub_driver']['road_time'][$driver->driver_id][$shipment->vehicle]+$road->road_time*$check_sub):0+$road->road_time*$check_sub;
                        }

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
                    $warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;
                    
                    if ($shipment->sub_driver == 1) {
                        $warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                    }

                    if ($shipment->sub_driver2 == 1) {
                        $warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                    }
                }
                
                
            }
        }

        

        
            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $objPHPExcel = new PHPExcel();

            

            $index_worksheet = 0; //(worksheet mặc định là 0, nếu tạo nhiều worksheet $index_worksheet += 1)
            $objPHPExcel->setActiveSheetIndex($index_worksheet)
               ->setCellValue('A1', 'STT')
               ->setCellValue('B1', 'XE')
               ->setCellValue('C1', 'HỌ TÊN')
               ->setCellValue('D1', 'DOANH THU')
               ->setCellValue('E1', 'LƯƠNG');
               

            

            
            
            

            if ($drivers) {

                $tongdoanhthu = 0; $tongluong=0; $tongphucap=0; $tongcong=0;   
    
                foreach ($drivers as $driver) {
                    $tongdoanhthu += isset($doanhthu[$driver->driver_id][$driver->vehicle])?$doanhthu[$driver->driver_id][$driver->vehicle]:0;
                    $tongphucap += ((isset($road_data['road_time'][$driver->driver_id][$driver->vehicle])?$road_data['road_time'][$driver->driver_id][$driver->vehicle]:0)-(isset($road_data['sub_driver']['road_time'][$driver->driver_id][$driver->vehicle])?$road_data['sub_driver']['road_time'][$driver->driver_id][$driver->vehicle]:0))*180000;

                }
                    $tongluong = $tongdoanhthu*0.098;
                    $conlai = $tongluong-$tongphucap;
                    $thang13 = $conlai*(100-8.3);
                    $tyle = $thang13/$tongluong/100;


                $hang = 2;
                $i=1;

                foreach ($drivers as $row) {
                    
                    


                    //$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$hang)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
                     $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $hang, $i++)
                        ->setCellValueExplicit('B' . $hang, $vehicle_data[$row->vehicle])
                        ->setCellValue('C' . $hang, $row->driver_name)
                        ->setCellValue('D' . $hang, $doanhthu[$row->driver_id][$row->vehicle])
                        ->setCellValue('E' . $hang, '=(D'.$hang.'*9.80%)*'.$tyle);
                     $hang++;


                  }

          }

            $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

            $highestRow ++;


            $objPHPExcel->getActiveSheet()->getStyle('D2:E'.$highestRow)->getNumberFormat()->setFormatCode("#,##0_);[Black](#,##0)");
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(16);
            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);

            // Set properties
            $objPHPExcel->getProperties()->setCreator("TCMT")
                            ->setLastModifiedBy($_SESSION['user_logined'])
                            ->setTitle("Salary Report")
                            ->setSubject("Salary Report")
                            ->setDescription("Salary Report.")
                            ->setKeywords("Salary Report")
                            ->setCategory("Salary Report");
            $objPHPExcel->getActiveSheet()->setTitle("Bang luong tai xe");

            $objPHPExcel->getActiveSheet()->freezePane('A1');
            $objPHPExcel->setActiveSheetIndex(0);



            

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename= BẢNG LƯƠNG TÀI XẾ.xlsx");
            header("Cache-Control: max-age=0");
            ob_clean();
            $objWriter->save("php://output");
        
    }

}
?>