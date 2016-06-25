<?php
Class nearlyController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Tổng hợp tuyến mới chạy gần nhất';

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
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'shipment_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'DESC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 50;
            $batdau = date('d-m-Y');
            $xe = "";

            
        }

        $ngay = date('d-m-Y', strtotime($batdau. ' + 2 days'));

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['sonews'] = 1;

        $vehicle_model = $this->model->get('vehicleModel');

        $vehicle_list = $vehicle_model->getAllVehicle();

        $this->view->data['vehicle_list'] = $vehicle_list;

        $data = array(
            'where' => '1=1'
        );

        if ($xe != "") {
            $data['where'] .= ' AND vehicle_id = '.$xe;
        }

        $vehicles = $vehicle_model->getAllVehicle($data);

        $this->view->data['vehicles'] = $vehicles;

        $this->view->data['batdau'] = $batdau;

        $this->view->data['xe'] = $xe;

        $shipment_model = $this->model->get('shipmentModel');
        
        $warehouse_model = $this->model->get('warehouseModel');
        
        $vehicle_data = array();
        $warehouse_data = array();

        foreach ($vehicles as $vehicle) {

            $query = 'SELECT * FROM shipment, customer WHERE shipment.customer=customer.customer_id AND shipment_ton > 0 AND shipment_charge > 0 AND vehicle='.$vehicle->vehicle_id.' AND shipment_date <= '.strtotime($ngay).' ORDER BY shipment_date DESC LIMIT 1';
            
            $shipments = $shipment_model->queryShipment($query);

            foreach ($shipments as $ship) {
                $vehicle_data[$vehicle->vehicle_id]['date'] = $ship->shipment_date;
                $vehicle_data[$vehicle->vehicle_id]['from'] = $ship->shipment_from;
                $vehicle_data[$vehicle->vehicle_id]['to'] = $ship->shipment_to;
                $vehicle_data[$vehicle->vehicle_id]['customer'] = $ship->customer_name;
                $vehicle_data[$vehicle->vehicle_id]['revenue'] = $ship->shipment_revenue+$ship->revenue_other;
                $vehicle_data[$vehicle->vehicle_id]['cost'] = $ship->shipment_cost;
                $vehicle_data[$vehicle->vehicle_id]['shipment'] = $ship->shipment_id;
                $vehicle_data[$vehicle->vehicle_id]['shipment_plan'] = $ship->shipment_plan;
                $vehicle_data[$vehicle->vehicle_id]['shipment_expect'] = $ship->shipment_expect;

                $warehouses = $warehouse_model->getAllWarehouse(array('where'=>'warehouse_id = '.$ship->shipment_from.' OR warehouse_id = '.$ship->shipment_to));
        
                
                foreach ($warehouses as $warehouse) {
                    
                        $warehouse_data['warehouse_id'][$warehouse->warehouse_id] = $warehouse->warehouse_id;
                        $warehouse_data['warehouse_name'][$warehouse->warehouse_id] = $warehouse->warehouse_name;
                    
                }
                
            }
        }

        $this->view->data['warehouse'] = $warehouse_data;
        $this->view->data['vehicle_data'] = $vehicle_data;
        
        $this->view->show('nearly/index');
    }

    public function shipmentplan(){
        
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['data'])) {

            $shipment = $this->model->get('shipmentModel');
            $shipment_data = $shipment->getShipment($_POST['data']);

            $data = array(
                        
                        'shipment_plan' => trim($_POST['keyword']),
                        );
          
            $shipment->updateShipment($data,array('shipment_id' => $_POST['data']));

            date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."shipment_plan"."|".$_POST['data']."|shipment|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

            return true;
                    
        }
    }

    public function shipmentexpect(){
        
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['data'])) {

            $shipment = $this->model->get('shipmentModel');
            $shipment_data = $shipment->getShipment($_POST['data']);

            $data = array(
                        
                        'shipment_expect' => trim($_POST['keyword']),
                        );
          
            $shipment->updateShipment($data,array('shipment_id' => $_POST['data']));

            date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."shipment_expect"."|".$_POST['data']."|shipment|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

            return true;
                    
        }
    }

    

}
?>