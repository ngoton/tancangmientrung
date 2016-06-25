<?php
Class salaryController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Bảng lương';

        $this->view->show('salary/index');
    }

    public function driver() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Bảng lương tài xế';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
            $trangthai = isset($_POST['sl_trangthai']) ? $_POST['sl_trangthai'] : null;
        }
        else{
           
            $batdau = date('m');
            
            $ketthuc = date('Y'); //cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).'-'.date('m-Y');
            $trangthai = -1;
            
        }

        $dauthang = '01-'.$batdau.'-'.$ketthuc;
        $cuoithang = date('t-'.$batdau.'-'.$ketthuc);

        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();
        foreach ($vehicles as $vehicle) {
            $vehicle_data[$vehicle->vehicle_id] = $vehicle->vehicle_number;
        }
        $this->view->data['vehicle_data'] = $vehicle_data;

        $d_data = array(
            'where'=> 'end_work > '.strtotime($dauthang),
            'order_by'=>'driver_name ASC',
        );
        if ($trangthai > 0) {
            $d_data['where'] .= ' AND driver_id = '.$trangthai;
        }
        $driver_model = $this->model->get('driverModel');
        $drivers = $driver_model->getAllDriver($d_data);

        $drivers_data = $driver_model->getAllDriver(array('order_by'=>'driver_name ASC'));
        $this->view->data['driver_data'] = $drivers_data;

        $shipment_model = $this->model->get('shipmentModel');
        $warehouse_model = $this->model->get('warehouseModel');
        $road_model = $this->model->get('roadModel');
        $steersman_model = $this->model->get('steersmanModel');
        $tax_model = $this->model->get('taxModel');
        $insurance_model = $this->model->get('insuranceModel');
        $overtime_model = $this->model->get('overtimeModel');
        $toxic_model = $this->model->get('toxicModel');

        $doanhthu = array();
        $chiphiphatsinh = array();
        $vuottai = array();
        $hoahong = array();

        $doanhthuthem = array();

        $warehouse_data = array();
        $road_data = array();

        foreach ($drivers as $driver) {

            $insurances[$driver->driver_id][$driver->vehicle] = $insurance_model->getInsuranceByWhere(array('insurance_date'=>strtotime($dauthang),'driver'=>$driver->driver_id));
            $taxs[$driver->driver_id][$driver->vehicle] = $tax_model->getTaxByWhere(array('tax_date'=>strtotime($dauthang),'driver'=>$driver->driver_id));
            $overtimes[$driver->driver_id][$driver->vehicle] = $overtime_model->getOvertimeByWhere(array('overtime_date'=>strtotime($dauthang),'driver'=>$driver->driver_id));
            $toxics[$driver->driver_id][$driver->vehicle] = $toxic_model->getToxicByWhere(array('toxic_date'=>strtotime($dauthang),'driver'=>$driver->driver_id));

            $steersmans[$driver->driver_id][$driver->vehicle] = $steersman_model->getSteersman($driver->steersman);
               
            $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');
            $shipments = $shipment_model->getAllShipment(array('where'=>'vehicle = '.$driver->vehicle.' AND shipment_date >= '.strtotime($dauthang).' AND shipment_date < '.strtotime($cuoithang)),$join);

            


            foreach ($shipments as $shipment) {
                $check_sub = 1;
               if ($shipment->shipment_sub==1) {
                   $check_sub = 0;
               }
                
                if($driver->end_work > $shipment->shipment_date && $shipment->shipment_date >= $driver->start_work){
                    if ($shipment->sub_driver == 1) {
                        $doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle] = isset($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle])?($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess) : (0+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess);
                    }
                    if ($shipment->sub_driver2 == 1) {
                        $doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle] = isset($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle])?($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess) : (0+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess);
                    }

                    $doanhthu[$driver->driver_id][$shipment->vehicle] = isset($doanhthu[$driver->driver_id][$shipment->vehicle])?($doanhthu[$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess) : (0+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess);
                    if ($shipment->customer == 117 && strtotime('01-06-2015') <= $shipment->shipment_date && $shipment->shipment_date <= strtotime('30-06-2015')) {
                        $doanhthuthem[$driver->driver_id][$shipment->vehicle] = isset($doanhthuthem[$driver->driver_id][$shipment->vehicle])?($doanhthuthem[$driver->driver_id][$shipment->vehicle]+33000) : (0+33000);
                    }
                    
                    $chiphiphatsinh[$driver->driver_id][$shipment->vehicle] = isset($chiphiphatsinh[$driver->driver_id][$shipment->vehicle])?($chiphiphatsinh[$driver->driver_id][$shipment->vehicle]+(($shipment->approve==1)?$shipment->cost_add:0)) : (0+(($shipment->approve==1)?$shipment->cost_add:0));
                    $vuottai[$driver->driver_id][$shipment->vehicle] = isset($vuottai[$driver->driver_id][$shipment->vehicle])?($vuottai[$driver->driver_id][$shipment->vehicle]+($shipment->shipment_bonus*$check_sub)) : (0+($shipment->shipment_bonus*$check_sub));
                    $hoahong[$driver->driver_id][$shipment->vehicle] = isset($hoahong[$driver->driver_id][$shipment->vehicle])?($hoahong[$driver->driver_id][$shipment->vehicle]+($shipment->commission*$shipment->commission_number)) : (0+$shipment->commission*$shipment->commission_number);

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

                    $canxe = 0;
                    $quetcont = 0;
                    $vecong = 0;
                    $boiduong = 0;

                    
                    foreach ($warehouse as $warehouse) {
                        if($shipment->shipment_to == $warehouse->warehouse_code){
                            $vecong += $warehouse->warehouse_gate;
                        }
                        if($shipment->shipment_ton > 0 && $chek_rong == 0){
                            $canxe += $warehouse->warehouse_weight;
                            $quetcont += $warehouse->warehouse_clean;
                        }

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
                                $boiduong += $warehouse->warehouse_add;
                            }
                            if ($warehouse->warehouse_ton != 0){
                                $boiduong_tan += $trongluong * $warehouse->warehouse_ton;
                                $boiduong += $trongluong * $warehouse->warehouse_ton;
                            }

                        }
                        else{
                            if ($shipment->shipment_ton > 0) {
                                $boiduong_cont += $warehouse->warehouse_add;
                                $boiduong += $warehouse->warehouse_add;
                            }
                        }
                    }
                    $warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;
                    $warehouse_data['boiduong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['boiduong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['boiduong'][$driver->driver_id][$shipment->vehicle]+$boiduong*$check_sub):0+$boiduong*$check_sub;
                    $warehouse_data['canxe'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['canxe'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['canxe'][$driver->driver_id][$shipment->vehicle]+$canxe*$check_sub):0+$canxe*$check_sub;
                    $warehouse_data['quetcont'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['quetcont'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['quetcont'][$driver->driver_id][$shipment->vehicle]+$quetcont*$check_sub):0+$quetcont*$check_sub;
                    $warehouse_data['vecong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['vecong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['vecong'][$driver->driver_id][$shipment->vehicle]+$vecong*$check_sub):0+$vecong*$check_sub;
                    
                    if ($shipment->sub_driver == 1) {
                        $warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                        $warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle]+$boiduong*$check_sub):0+$boiduong*$check_sub;
                        $warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle]+$canxe*$check_sub):0+$canxe*$check_sub;
                        $warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle]+$quetcont*$check_sub):0+$quetcont*$check_sub;
                        $warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle]+$vecong*$check_sub):0+$vecong*$check_sub;
                    }

                    if ($shipment->sub_driver2 == 1) {
                        $warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                        $warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle]+$boiduong*$check_sub):0+$boiduong*$check_sub;
                        $warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle]+$canxe*$check_sub):0+$canxe*$check_sub;
                        $warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle]+$quetcont*$check_sub):0+$quetcont*$check_sub;
                        $warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle]+$vecong*$check_sub):0+$vecong*$check_sub;
                    }
                }
                
                
            }
        }

        $this->view->data['drivers'] = $drivers;
        $this->view->data['warehouse'] = $warehouse_data;
        $this->view->data['road'] = $road_data;

        $this->view->data['doanhthu'] = $doanhthu;
        $this->view->data['doanhthuthem'] = $doanhthuthem;
        $this->view->data['chiphiphatsinh'] = $chiphiphatsinh;

        $this->view->data['vuottai'] = $vuottai;
        $this->view->data['hoahong'] = $hoahong;

        $this->view->data['steersmans'] = $steersmans;
        $this->view->data['insurances'] = $insurances;
        $this->view->data['taxs'] = $taxs;
        $this->view->data['overtimes'] = $overtimes;
        $this->view->data['toxics'] = $toxics;

        $this->view->data['batdau'] = $batdau;
        $this->view->data['ketthuc'] = $ketthuc;
        $this->view->data['trangthai'] = $trangthai;
        $this->view->show('salary/driver');
    }

    public function insurance() {
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $insurance_model = $this->model->get('insuranceModel');
            $data = array(
                'insurance_date' => strtotime($_POST['insurance_date']),
                'driver' => $_POST['driver'],
                'money' => trim(str_replace(',','',$_POST['money'])),

            );

            if ($insurance_model->getInsuranceByWhere(array('insurance_date'=>$data['insurance_date'],'driver'=>$data['driver']))) {
                $insurance_model->updateInsurance($data,array('insurance_date'=>$data['insurance_date'],'driver'=>$data['driver']));
            }
            elseif (!$insurance_model->getInsuranceByWhere(array('insurance_date'=>$data['insurance_date'],'driver'=>$data['driver']))) {
                $insurance_model->createInsurance($data);
            }
        }
    }

    public function tax() {
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tax_model = $this->model->get('taxModel');
            $data = array(
                'tax_date' => strtotime($_POST['tax_date']),
                'driver' => $_POST['driver'],
                'money' => trim(str_replace(',','',$_POST['money'])),

            );

            if ($tax_model->getTaxByWhere(array('tax_date'=>$data['tax_date'],'driver'=>$data['driver']))) {
                $tax_model->updateTax($data,array('tax_date'=>$data['tax_date'],'driver'=>$data['driver']));
            }
            elseif (!$tax_model->getTaxByWhere(array('tax_date'=>$data['tax_date'],'driver'=>$data['driver']))) {
                $tax_model->createTax($data);
            }
        }
    }

    public function overtime() {
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $overtime_model = $this->model->get('overtimeModel');
            $data = array(
                'overtime_date' => strtotime($_POST['overtime_date']),
                'driver' => $_POST['driver'],
                'money' => trim(str_replace(',','',$_POST['money'])),

            );

            if ($overtime_model->getOvertimeByWhere(array('overtime_date'=>$data['overtime_date'],'driver'=>$data['driver']))) {
                $overtime_model->updateOvertime($data,array('overtime_date'=>$data['overtime_date'],'driver'=>$data['driver']));
            }
            elseif (!$overtime_model->getOvertimeByWhere(array('overtime_date'=>$data['overtime_date'],'driver'=>$data['driver']))) {
                $overtime_model->createOvertime($data);
            }
        }
    }

    public function toxic() {
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $toxic_model = $this->model->get('toxicModel');
            $data = array(
                'toxic_date' => strtotime($_POST['toxic_date']),
                'driver' => $_POST['driver'],
                'money' => trim(str_replace(',','',$_POST['money'])),

            );

            if ($toxic_model->getToxicByWhere(array('toxic_date'=>$data['toxic_date'],'driver'=>$data['driver']))) {
                $toxic_model->updateToxic($data,array('toxic_date'=>$data['toxic_date'],'driver'=>$data['driver']));
            }
            elseif (!$toxic_model->getToxicByWhere(array('toxic_date'=>$data['toxic_date'],'driver'=>$data['driver']))) {
                $toxic_model->createToxic($data);
            }
        }
    }

    public function view() {
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Bảng lương tài xế';

        if ($this->registry->router->param_id != null && $this->registry->router->page != null && $this->registry->router->order_by != null) {
            $trangthai = $this->registry->router->param_id;
            $batdau = $this->registry->router->page;
            $ketthuc = $this->registry->router->order_by;

            $dauthang = '01-'.$batdau.'-'.$ketthuc;
            $cuoithang = date('t-'.$batdau.'-'.$ketthuc);

            $d_data = array(
                'where'=> 'end_work > '.strtotime($dauthang).' AND driver_id = '.$trangthai,
                
            );
            $driver_model = $this->model->get('driverModel');
            $drivers = $driver_model->getAllDriver($d_data);
            
            $shipment_model = $this->model->get('shipmentModel');
            $warehouse_model = $this->model->get('warehouseModel');
            $road_model = $this->model->get('roadModel');

            $shipments = array();

            $warehouse_data = array();
            $road_data = array();

            foreach ($drivers as $driver) {
                $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');
                $shipments = $shipment_model->getAllShipment(array('where'=>'vehicle = '.$driver->vehicle.' AND shipment_date >= '.strtotime($dauthang).' AND shipment_date < '.strtotime($cuoithang)),$join);

                


                foreach ($shipments as $shipment) {
                    $check_sub = 1;
                   if ($shipment->shipment_sub==1) {
                       $check_sub = 0;
                   }
                    
                    if($driver->end_work > $shipment->shipment_date && $shipment->shipment_date >= $driver->start_work){
                        
                        $roads = $road_model->getAllRoad(array('where'=>'road_from = '.$shipment->shipment_from.' AND road_to = '.$shipment->shipment_to.' AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));
                        
                        $check_rong = 0;
                        
                        foreach ($roads as $road) {
                            $road_data['bridge_cost'][$shipment->shipment_id] = isset($road_data['bridge_cost'][$shipment->shipment_id])?($road_data['bridge_cost'][$shipment->shipment_id]+$road->bridge_cost*$check_sub):0+$road->bridge_cost*$check_sub;
                            $road_data['police_cost'][$shipment->shipment_id] = isset($road_data['police_cost'][$shipment->shipment_id])?($road_data['police_cost'][$shipment->shipment_id]+$road->police_cost*$check_sub):0+$road->police_cost*$check_sub;
                            $road_data['oil_cost'][$shipment->shipment_id] = isset($road_data['oil_cost'][$shipment->shipment_id])?($road_data['oil_cost'][$shipment->shipment_id]+($road->road_oil*$shipment->oil_cost)*$check_sub):0+($road->road_oil*$shipment->oil_cost)*$check_sub;
                            $road_data['road_time'][$shipment->shipment_id] = isset($road_data['road_time'][$shipment->shipment_id])?($road_data['road_time'][$shipment->shipment_id]+$road->road_time*$check_sub):0+$road->road_time*$check_sub;
                            $road_data['tire_cost'][$shipment->shipment_id] = isset($road_data['tire_cost'][$shipment->shipment_id])?($road_data['tire_cost'][$shipment->shipment_id]+$road->tire_cost*$check_sub):0+$road->tire_cost*$check_sub;
                            if ($shipment->sub_driver == 1) {
                                $road_data['sub_driver']['police_cost'][$shipment->shipment_id] = isset($road_data['sub_driver']['police_cost'][$shipment->shipment_id])?($road_data['sub_driver']['police_cost'][$shipment->shipment_id]+$road->police_cost*$check_sub):0+$road->police_cost*$check_sub;
                                $road_data['sub_driver']['road_time'][$shipment->shipment_id] = isset($road_data['sub_driver']['road_time'][$shipment->shipment_id])?($road_data['sub_driver']['road_time'][$shipment->shipment_id]+$road->road_time*$check_sub):0+$road->road_time*$check_sub;
                            }

                            if ($shipment->sub_driver2 == 1) {
                                $road_data['sub_driver']['police_cost'][$shipment->shipment_id] = isset($road_data['sub_driver']['police_cost'][$shipment->shipment_id])?($road_data['sub_driver']['police_cost'][$shipment->shipment_id]+$road->police_cost*$check_sub):0+$road->police_cost*$check_sub;
                                $road_data['sub_driver']['road_time'][$shipment->shipment_id] = isset($road_data['sub_driver']['road_time'][$shipment->shipment_id])?($road_data['sub_driver']['road_time'][$shipment->shipment_id]+$road->road_time*$check_sub):0+$road->road_time*$check_sub;
                            }

                            $chek_rong = ($road->way == 0)?1:0;
                        }


                        $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$shipment->shipment_from.' OR warehouse_code = '.$shipment->shipment_to.') AND start_time <= '.$shipment->shipment_date.' AND end_time >= '.$shipment->shipment_date));
                    

                        $boiduong_cont = 0;
                        $boiduong_tan = 0;

                        $canxe = 0;
                        $quetcont = 0;
                        $vecong = 0;
                        $boiduong = 0;

                        
                        foreach ($warehouse as $warehouse) {
                            $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;
                            $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name; 
                            if($shipment->shipment_to == $warehouse->warehouse_code){
                                $vecong += $warehouse->warehouse_gate;
                            }
                            if($shipment->shipment_ton > 0 && $chek_rong == 0){
                                $canxe += $warehouse->warehouse_weight;
                                $quetcont += $warehouse->warehouse_clean;
                            }

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
                                    $boiduong += $warehouse->warehouse_add;
                                }
                                if ($warehouse->warehouse_ton != 0){
                                    $boiduong_tan += $trongluong * $warehouse->warehouse_ton;
                                    $boiduong += $trongluong * $warehouse->warehouse_ton;
                                }

                            }
                            else{
                                if ($shipment->shipment_ton > 0) {
                                    $boiduong_cont += $warehouse->warehouse_add;
                                    $boiduong += $warehouse->warehouse_add;
                                }
                            }
                        }
                        $warehouse_data['boiduong_cn'][$shipment->shipment_id] = isset($warehouse_data['boiduong_cn'][$shipment->shipment_id])?($warehouse_data['boiduong_cn'][$shipment->shipment_id]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;
                        $warehouse_data['boiduong'][$shipment->shipment_id] = isset($warehouse_data['boiduong'][$shipment->shipment_id])?($warehouse_data['boiduong'][$shipment->shipment_id]+$boiduong*$check_sub):0+$boiduong*$check_sub;
                        $warehouse_data['canxe'][$shipment->shipment_id] = isset($warehouse_data['canxe'][$shipment->shipment_id])?($warehouse_data['canxe'][$shipment->shipment_id]+$canxe*$check_sub):0+$canxe*$check_sub;
                        $warehouse_data['quetcont'][$shipment->shipment_id] = isset($warehouse_data['quetcont'][$shipment->shipment_id])?($warehouse_data['quetcont'][$shipment->shipment_id]+$quetcont*$check_sub):0+$quetcont*$check_sub;
                        $warehouse_data['vecong'][$shipment->shipment_id] = isset($warehouse_data['vecong'][$shipment->shipment_id])?($warehouse_data['vecong'][$shipment->shipment_id]+$vecong*$check_sub):0+$vecong*$check_sub;
                        
                        if ($shipment->sub_driver == 1) {
                            $warehouse_data['sub_driver']['boiduong_cn'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['boiduong_cn'][$shipment->shipment_id])?($warehouse_data['sub_driver']['boiduong_cn'][$shipment->shipment_id]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                            $warehouse_data['sub_driver']['boiduong'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['boiduong'][$shipment->shipment_id])?($warehouse_data['sub_driver']['boiduong'][$shipment->shipment_id]+$boiduong*$check_sub):0+$boiduong*$check_sub;
                            $warehouse_data['sub_driver']['canxe'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['canxe'][$shipment->shipment_id])?($warehouse_data['sub_driver']['canxe'][$shipment->shipment_id]+$canxe*$check_sub):0+$canxe*$check_sub;
                            $warehouse_data['sub_driver']['quetcont'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['quetcont'][$shipment->shipment_id])?($warehouse_data['sub_driver']['quetcont'][$shipment->shipment_id]+$quetcont*$check_sub):0+$quetcont*$check_sub;
                            $warehouse_data['sub_driver']['vecong'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['vecong'][$shipment->shipment_id])?($warehouse_data['sub_driver']['vecong'][$shipment->shipment_id]+$vecong*$check_sub):0+$vecong*$check_sub;
                        }

                        if ($shipment->sub_driver2 == 1) {
                            $warehouse_data['sub_driver']['boiduong_cn'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['boiduong_cn'][$shipment->shipment_id])?($warehouse_data['sub_driver']['boiduong_cn'][$shipment->shipment_id]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                            $warehouse_data['sub_driver']['boiduong'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['boiduong'][$shipment->shipment_id])?($warehouse_data['sub_driver']['boiduong'][$shipment->shipment_id]+$boiduong*$check_sub):0+$boiduong*$check_sub;
                            $warehouse_data['sub_driver']['canxe'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['canxe'][$shipment->shipment_id])?($warehouse_data['sub_driver']['canxe'][$shipment->shipment_id]+$canxe*$check_sub):0+$canxe*$check_sub;
                            $warehouse_data['sub_driver']['quetcont'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['quetcont'][$shipment->shipment_id])?($warehouse_data['sub_driver']['quetcont'][$shipment->shipment_id]+$quetcont*$check_sub):0+$quetcont*$check_sub;
                            $warehouse_data['sub_driver']['vecong'][$shipment->shipment_id] = isset($warehouse_data['sub_driver']['vecong'][$shipment->shipment_id])?($warehouse_data['sub_driver']['vecong'][$shipment->shipment_id]+$vecong*$check_sub):0+$vecong*$check_sub;
                        }
                    }
                    
                    
                }
            }

            $this->view->data['drivers'] = $drivers;
            $this->view->data['warehouse'] = $warehouse_data;
            $this->view->data['road'] = $road_data;

            $this->view->data['shipments'] = $shipments;

            $this->view->data['batdau'] = $batdau;
            $this->view->data['ketthuc'] = $ketthuc;
            $this->view->data['trangthai'] = $trangthai;

        }

        
        $this->view->show('salary/view');
    }



   function exportdriver(){
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }

        $batdau = $this->registry->router->param_id;
        $ketthuc = $this->registry->router->page;
        $trangthai = $this->registry->router->order_by;
        
        $dauthang = '01-'.$batdau.'-'.$ketthuc;
        $cuoithang = date('t-'.$batdau.'-'.$ketthuc);

        $vehicle_model = $this->model->get('vehicleModel');
        $vehicles = $vehicle_model->getAllVehicle();
        foreach ($vehicles as $vehicle) {
            $vehicle_data[$vehicle->vehicle_id] = $vehicle->vehicle_number;
        }
        

        $d_data = array(
            'where'=> 'end_work > '.strtotime($dauthang),
            'order_by'=>'driver_name ASC',
        );
        if ($trangthai > 0) {
            $d_data['where'] .= ' AND driver_id = '.$trangthai;
        }
        $driver_model = $this->model->get('driverModel');
        $drivers = $driver_model->getAllDriver($d_data);


        $shipment_model = $this->model->get('shipmentModel');
        $warehouse_model = $this->model->get('warehouseModel');
        $road_model = $this->model->get('roadModel');
        $steersman_model = $this->model->get('steersmanModel');
        $tax_model = $this->model->get('taxModel');
        $insurance_model = $this->model->get('insuranceModel');
        $overtime_model = $this->model->get('overtimeModel');
        $toxic_model = $this->model->get('toxicModel');

        $doanhthu = array();
        $doanhthuthem = array();
        $chiphiphatsinh = array();
        $vuottai = array();
        $hoahong = array();

        $warehouse_data = array();
        $road_data = array();

        foreach ($drivers as $driver) {

            $insurances[$driver->driver_id][$driver->vehicle] = $insurance_model->getInsuranceByWhere(array('insurance_date'=>strtotime($dauthang),'driver'=>$driver->driver_id));
            $taxs[$driver->driver_id][$driver->vehicle] = $tax_model->getTaxByWhere(array('tax_date'=>strtotime($dauthang),'driver'=>$driver->driver_id));
            $overtimes[$driver->driver_id][$driver->vehicle] = $overtime_model->getOvertimeByWhere(array('overtime_date'=>strtotime($dauthang),'driver'=>$driver->driver_id));
            $toxics[$driver->driver_id][$driver->vehicle] = $toxic_model->getToxicByWhere(array('toxic_date'=>strtotime($dauthang),'driver'=>$driver->driver_id));

            $steersmans[$driver->driver_id][$driver->vehicle] = $steersman_model->getSteersman($driver->steersman);
               
            $join = array('table'=>'customer, vehicle','where'=>'customer.customer_id = shipment.customer AND vehicle.vehicle_id = shipment.vehicle');
            $shipments = $shipment_model->getAllShipment(array('where'=>'vehicle = '.$driver->vehicle.' AND shipment_date >= '.strtotime($dauthang).' AND shipment_date < '.strtotime($cuoithang)),$join);

            


            foreach ($shipments as $shipment) {
                $check_sub = 1;
               if ($shipment->shipment_sub==1) {
                   $check_sub = 0;
               }
                
                if($driver->end_work > $shipment->shipment_date && $shipment->shipment_date >= $driver->start_work){
                    if ($shipment->sub_driver == 1) {
                        $doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle] = isset($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle])?($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess) : (0+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess);
                    }
                    if ($shipment->sub_driver2 == 1) {
                        $doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle] = isset($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle])?($doanhthu['sub_driver'][$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess) : (0+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess);
                    }

                    $doanhthu[$driver->driver_id][$shipment->vehicle] = isset($doanhthu[$driver->driver_id][$shipment->vehicle])?($doanhthu[$driver->driver_id][$shipment->vehicle]+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess) : (0+$shipment->shipment_revenue+$shipment->revenue_other+$shipment->shipment_charge_excess);
                    if ($shipment->customer == 117 && strtotime('01-06-2015') <= $shipment->shipment_date && $shipment->shipment_date <= strtotime('30-06-2015')) {
                        $doanhthuthem[$driver->driver_id][$shipment->vehicle] = isset($doanhthuthem[$driver->driver_id][$shipment->vehicle])?($doanhthuthem[$driver->driver_id][$shipment->vehicle]+33000) : (0+33000);
                    }
                    $chiphiphatsinh[$driver->driver_id][$shipment->vehicle] = isset($chiphiphatsinh[$driver->driver_id][$shipment->vehicle])?($chiphiphatsinh[$driver->driver_id][$shipment->vehicle]+(($shipment->approve==1)?$shipment->cost_add:0)) : (0+(($shipment->approve==1)?$shipment->cost_add:0));
                    $vuottai[$driver->driver_id][$shipment->vehicle] = isset($vuottai[$driver->driver_id][$shipment->vehicle])?($vuottai[$driver->driver_id][$shipment->vehicle]+($shipment->shipment_bonus*$check_sub)) : (0+($shipment->shipment_bonus*$check_sub));
                    $hoahong[$driver->driver_id][$shipment->vehicle] = isset($hoahong[$driver->driver_id][$shipment->vehicle])?($hoahong[$driver->driver_id][$shipment->vehicle]+($shipment->commission*$shipment->commission_number)) : (0+$shipment->commission*$shipment->commission_number);

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

                    $canxe = 0;
                    $quetcont = 0;
                    $vecong = 0;
                    $boiduong = 0;

                    
                    foreach ($warehouse as $warehouse) {
                        if($shipment->shipment_to == $warehouse->warehouse_code){
                            $vecong += $warehouse->warehouse_gate;
                        }
                        if($shipment->shipment_ton > 0 && $chek_rong == 0){
                            $canxe += $warehouse->warehouse_weight;
                            $quetcont += $warehouse->warehouse_clean;
                        }

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
                                $boiduong += $warehouse->warehouse_add;
                            }
                            if ($warehouse->warehouse_ton != 0){
                                $boiduong_tan += $trongluong * $warehouse->warehouse_ton;
                                $boiduong += $trongluong * $warehouse->warehouse_ton;
                            }

                        }
                        else{
                            if ($shipment->shipment_ton > 0) {
                                $boiduong_cont += $warehouse->warehouse_add;
                                $boiduong += $warehouse->warehouse_add;
                            }
                        }
                    }
                    $warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;
                    $warehouse_data['boiduong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['boiduong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['boiduong'][$driver->driver_id][$shipment->vehicle]+$boiduong*$check_sub):0+$boiduong*$check_sub;
                    $warehouse_data['canxe'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['canxe'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['canxe'][$driver->driver_id][$shipment->vehicle]+$canxe*$check_sub):0+$canxe*$check_sub;
                    $warehouse_data['quetcont'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['quetcont'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['quetcont'][$driver->driver_id][$shipment->vehicle]+$quetcont*$check_sub):0+$quetcont*$check_sub;
                    $warehouse_data['vecong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['vecong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['vecong'][$driver->driver_id][$shipment->vehicle]+$vecong*$check_sub):0+$vecong*$check_sub;
                    
                    if ($shipment->sub_driver == 1) {
                        $warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                        $warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle]+$boiduong*$check_sub):0+$boiduong*$check_sub;
                        $warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle]+$canxe*$check_sub):0+$canxe*$check_sub;
                        $warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle]+$quetcont*$check_sub):0+$quetcont*$check_sub;
                        $warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle]+$vecong*$check_sub):0+$vecong*$check_sub;
                    }

                    if ($shipment->sub_driver2 == 1) {
                        $warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong_cn'][$driver->driver_id][$shipment->vehicle]+($boiduong_cont+$boiduong_tan)*$check_sub):0+($boiduong_cont+$boiduong_tan)*$check_sub;    
                        $warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['boiduong'][$driver->driver_id][$shipment->vehicle]+$boiduong*$check_sub):0+$boiduong*$check_sub;
                        $warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['canxe'][$driver->driver_id][$shipment->vehicle]+$canxe*$check_sub):0+$canxe*$check_sub;
                        $warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['quetcont'][$driver->driver_id][$shipment->vehicle]+$quetcont*$check_sub):0+$quetcont*$check_sub;
                        $warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle] = isset($warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle])?($warehouse_data['sub_driver']['vecong'][$driver->driver_id][$shipment->vehicle]+$vecong*$check_sub):0+$vecong*$check_sub;
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
               ->setCellValue('B1', 'MÃ NV')
               ->setCellValue('C1', 'XE')
               ->setCellValue('D1', 'HỌ TÊN')
               ->setCellValue('E1', 'TK NGÂN HÀNG')
               ->setCellValue('F1', 'TỔNG NGÀY CÔNG')
               ->setCellValue('G1', 'LƯƠNG')
               ->setCellValue('G2', 'DOANH THU')
               ->setCellValue('H2', 'CHI PHÍ 0 VAT')
               ->setCellValue('I2', 'DOANH THU TÍNH LƯƠNG')
               ->setCellValue('J2', 'LƯƠNG CỐ ĐỊNH')
               ->setCellValue('K2', 'LƯƠNG SẢN PHẨM')
               ->setCellValue('L1', 'PHỤ CẤP')
               ->setCellValue('L2', 'ĂN CA')
               ->setCellValue('M2', 'LÀM ĐÊM')
               ->setCellValue('N2', 'ĐỘC HẠI')
               ->setCellValue('O1', 'KHẤU TRỪ')
               ->setCellValue('O2', 'BẢO HIỂM')
               ->setCellValue('P2', 'THUẾ')
               ->setCellValue('Q2', 'CÔNG ĐOÀN')
               ->setCellValue('R1', 'TỔNG CỘNG');
               

            

            
            
            

            if ($drivers) {

                $tongdoanhthu = 0; $tongchiphi=0; $tongluongsp=0; $tongluongcd=0; $tongbh=0; $tongthue=0; $tongcongdoan=0; $tongsk=0; $tongphucap=0; $tongcong=0; $tongthuong=0; $tonghoahong=0;  $tonganca=0;
           $luongt13 =  0; $bh =  640000; $thue =  175729; $sk =  130417;
        $tongdoanhthuthem=0; $tongdoanhthutinhluong=0;
    
            
                $hang = 3;
                $i=1;

                foreach ($drivers as $driver) {
                    
                    $chiphi = isset($road_data['police_cost'][$driver->driver_id][$driver->vehicle])?$road_data['police_cost'][$driver->driver_id][$driver->vehicle]:0;
                    $chiphi += isset($road_data['tire_cost'][$driver->driver_id][$driver->vehicle])?$road_data['tire_cost'][$driver->driver_id][$driver->vehicle]:0;
                    $chiphi += isset($chiphiphatsinh[$driver->driver_id][$driver->vehicle])?$chiphiphatsinh[$driver->driver_id][$driver->vehicle]:0;
                    
                    $chiphi += isset($hoahong[$driver->driver_id][$driver->vehicle])?$hoahong[$driver->driver_id][$driver->vehicle]:0;
                    
                    $chiphi += isset($warehouse_data['boiduong'][$driver->driver_id][$driver->vehicle])?$warehouse_data['boiduong'][$driver->driver_id][$driver->vehicle]:0;
                    $chiphi += isset($warehouse_data['canxe'][$driver->driver_id][$driver->vehicle])?$warehouse_data['canxe'][$driver->driver_id][$driver->vehicle]:0;
                    $chiphi += isset($warehouse_data['quetcont'][$driver->driver_id][$driver->vehicle])?$warehouse_data['quetcont'][$driver->driver_id][$driver->vehicle]:0;
                    $chiphi += isset($warehouse_data['vecong'][$driver->driver_id][$driver->vehicle])?$warehouse_data['vecong'][$driver->driver_id][$driver->vehicle]:0;

                    

                    $ngaycong = round(isset($road_data['road_time'][$driver->driver_id][$driver->vehicle])?$road_data['road_time'][$driver->driver_id][$driver->vehicle]:0);

                    $bh = isset($insurances[$driver->driver_id][$driver->vehicle]->money)?$insurances[$driver->driver_id][$driver->vehicle]->money:0;
                    $thue = isset($taxs[$driver->driver_id][$driver->vehicle]->money)?$taxs[$driver->driver_id][$driver->vehicle]->money:0;
                    $lamdem = isset($overtimes[$driver->driver_id][$driver->vehicle]->money)?$overtimes[$driver->driver_id][$driver->vehicle]->money:0;
                    $dochai = isset($toxics[$driver->driver_id][$driver->vehicle]->money)?$toxics[$driver->driver_id][$driver->vehicle]->money:0;

                    $ngayvaocang = isset($steersmans[$driver->driver_id][$driver->vehicle])?$steersmans[$driver->driver_id][$driver->vehicle]->steersman_start_time:0;

                    $timeDiff = strtotime(date('t-m-Y',strtotime('01-'.$batdau.'-'.$ketthuc))) - $ngayvaocang;

                    $numberDays = $timeDiff/86400;  // 86400 seconds in one day

                    // and you might want to convert to integer
                    $numberDays = intval($numberDays); 

                    $timeDiff2 = strtotime(date('t-m-Y',strtotime('01-'.$batdau.'-'.$ketthuc))) - $driver->start_work;

                    $numberDays2 = $timeDiff2/86400;  // 86400 seconds in one day

                    // and you might want to convert to integer
                    $numberDays2 = intval($numberDays2); 

                    if ($numberDays >= 30) {
                        $luongcd = 2000000;
                    }
                    else{
                        $luongcd = $numberDays2>0?round(2000000*$numberDays2/26):0;
                    }

                    if($luongcd>2000000)
                        $luongcd = 2000000;

        
                    //$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$hang)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
                     $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $hang, $i++)
                        ->setCellValue('B' . $hang, $driver->driver_code)
                        ->setCellValueExplicit('C' . $hang, $vehicle_data[$driver->vehicle])
                        ->setCellValue('D' . $hang, $driver->driver_name)
                        ->setCellValue('E' . $hang, "'".$driver->driver_bank)
                        ->setCellValue('F' . $hang, $ngaycong)
                        ->setCellValue('G' . $hang, isset($doanhthu[$driver->driver_id][$driver->vehicle])?$doanhthu[$driver->driver_id][$driver->vehicle]:0)
                        ->setCellValue('H' . $hang, $chiphi)
                        ->setCellValue('I' . $hang, '=G'.$hang.'-H'.$hang.'+'.(isset($doanhthuthem[$driver->driver_id][$driver->vehicle])?$doanhthuthem[$driver->driver_id][$driver->vehicle]:0))
                        ->setCellValue('J' . $hang, $luongcd)
                        ->setCellValue('K' . $hang, '=ROUND(I'.$hang.'*10%,0)')
                        ->setCellValue('L' . $hang, '=F'.$hang.'*25000')
                        ->setCellValue('M' . $hang, $lamdem)
                        ->setCellValue('N' . $hang, $dochai)
                        ->setCellValue('O' . $hang, $bh)
                        ->setCellValue('P' . $hang, $thue)
                        ->setCellValue('Q' . $hang, '=IF(((J'.$hang.'+K'.$hang.'-O'.$hang.'-P'.$hang.')*1%) > 115000,115000,ROUND((J'.$hang.'+K'.$hang.'-O'.$hang.'-P'.$hang.')*1%,0) )')
                        ->setCellValue('R' . $hang, '=SUM(J'.$hang.':N'.$hang.')-SUM(O'.$hang.':Q'.$hang.')');
                     $hang++;


                  }

          }

            $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

            $highestRow ++;

            $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
            $objPHPExcel->getActiveSheet()->mergeCells('B1:B2');
            $objPHPExcel->getActiveSheet()->mergeCells('C1:C2');
            $objPHPExcel->getActiveSheet()->mergeCells('D1:D2');
            $objPHPExcel->getActiveSheet()->mergeCells('E1:E2');
            $objPHPExcel->getActiveSheet()->mergeCells('F1:F2');
            $objPHPExcel->getActiveSheet()->mergeCells('G1:K1');
            $objPHPExcel->getActiveSheet()->mergeCells('L1:N1');
            $objPHPExcel->getActiveSheet()->mergeCells('O1:Q1');
            $objPHPExcel->getActiveSheet()->mergeCells('R1:R2');

            $objPHPExcel->getActiveSheet()->getStyle('G2:R'.$highestRow)->getNumberFormat()->setFormatCode("#,##0_);[Black](#,##0)");
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFont()->setBold(true);
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

            $objPHPExcel->getActiveSheet()->freezePane('A3');
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