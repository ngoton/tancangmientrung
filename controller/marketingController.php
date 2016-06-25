<?php
Class marketingController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 6 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Kinh doanh';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'marketing_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
            $batdau = '01-'.date('m-Y');
            $ketthuc = date('t-m-Y');
        }

        $join = array('table'=>'customer','where'=>'customer.customer_id = marketing.customer');

        $marketing_model = $this->model->get('marketingModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;

        $data = array(
            'where' => 'marketing_date >= '.strtotime($batdau).' AND marketing_date <= '.strtotime($ketthuc),
            );
        
        $tongsodong = count($marketing_model->getAllMarketing($data,$join));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['limit'] = $limit;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['sonews'] = $sonews;

        $this->view->data['batdau'] = $batdau;
        $this->view->data['ketthuc'] = $ketthuc;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => 'marketing_date >= '.strtotime($batdau).' AND marketing_date <= '.strtotime($ketthuc),
            );
        
        if ($keyword != '') {
            $search = '( 
                    OR customer_name LIKE "%'.$keyword.'%"
                    OR marketing_from in (SELECT warehouse_id FROM warehouse WHERE warehouse_name LIKE "%'.$keyword.'%" ) 
                    OR marketing_to in (SELECT warehouse_id FROM warehouse WHERE warehouse_name LIKE "%'.$keyword.'%" ) 
                 )';
            $data['where'] = $search;
        }
        
        $marketing_data = $marketing_model->getAllMarketing($data, $join);
        
        $this->view->data['marketings'] = $marketing_data;

        $this->view->data['lastID'] = isset($marketing_model->getLastMarketing()->marketing_id)?$marketing_model->getLastMarketing()->marketing_id:0;

        $warehouse_model = $this->model->get('warehouseModel');

        $warehouse_data = array();

        foreach ($marketing_data as $ship) {
            

            $warehouse = $warehouse_model->getAllWarehouse(array('where'=>'(warehouse_code = '.$ship->marketing_from.' OR warehouse_code = '.$ship->marketing_to.') AND start_time <= '.$ship->marketing_date.' AND end_time >= '.$ship->marketing_date));
        

            foreach ($warehouse as $warehouse) {
                
                    $warehouse_data['warehouse_id'][$warehouse->warehouse_code] = $warehouse->warehouse_code;
                    $warehouse_data['warehouse_name'][$warehouse->warehouse_code] = $warehouse->warehouse_name;
                
                
            }
        }

        $this->view->data['warehouse'] = $warehouse_data;
        
        $this->view->show('marketing/index');
    }

    public function view($id) {
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }

        if (!$id) {
            return $this->view->redirect('marketing');
        }
        
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Lô hàng đã nhận';

        $join = array('table'=>'user','where'=>'user.user_id = shipment_temp.owner');

        $shipment_temp_model = $this->model->get('shipmenttempModel');

        $data = array(
            'where' => 'marketing = '.$id,
            'order_by' => 'shipment_temp_date ASC',
            );
    
        
        $shipment_temp_data = $shipment_temp_model->getAllShipment($data, $join);
        
        $this->view->data['shipment_temps'] = $shipment_temp_data;
        
        $this->view->show('marketing/view');
    }

    public function getshipmentfrom(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 6 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $warehouse_model = $this->model->get('warehouseModel');
            
            if ($_POST['keyword'] == "*") {
                $data = array(
                'where'=>'( check_new IS NULL )',
                );
                $list = $warehouse_model->getAllWarehouse($data);
            }
            else{
                $data = array(
                'where'=>'( warehouse_name LIKE "'.$_POST['keyword'].'%" AND check_new IS NULL )',
                );
                $list = $warehouse_model->getAllWarehouse($data);
            }
            
            foreach ($list as $rs) {
                // put in bold the written text
                $warehouse_name = $rs->warehouse_name;
                if ($_POST['keyword'] != "*") {
                    $warehouse_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->warehouse_name);
                }
                
                // add new option
                echo '<li onclick="set_item_shipment_from(\''.$rs->warehouse_id.'\',\''.$rs->warehouse_name.'\')">'.$warehouse_name.'</li>';
            }
        }
    }
    public function getshipmentto(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 6 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $warehouse_model = $this->model->get('warehouseModel');
            
            if ($_POST['keyword'] == "*") {
                $data = array(
                'where'=>'( check_new IS NULL )',
                );
                $list = $warehouse_model->getAllWarehouse($data);
            }
            else{
                $data = array(
                'where'=>'( warehouse_name LIKE "'.$_POST['keyword'].'%" AND check_new IS NULL )',
                );
                $list = $warehouse_model->getAllWarehouse($data);
            }
            
            foreach ($list as $rs) {
                // put in bold the written text
                $warehouse_name = $rs->warehouse_name;
                if ($_POST['keyword'] != "*") {
                    $warehouse_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->warehouse_name);
                }
                
                // add new option
                echo '<li onclick="set_item_shipment_to(\''.$rs->warehouse_id.'\',\''.$rs->warehouse_name.'\')">'.$warehouse_name.'</li>';
            }
        }
    }
    public function getcustomer(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 6 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $customer_model = $this->model->get('customerModel');
            
            if ($_POST['keyword'] == "*") {

                $list = $customer_model->getAllCustomer();
            }
            else{
                $data = array(
                'where'=>'( customer_name LIKE "%'.$_POST['keyword'].'%" )',
                );
                $list = $customer_model->getAllCustomer($data);
            }
            
            foreach ($list as $rs) {
                // put in bold the written text
                $customer_name = $rs->customer_name;
                if ($_POST['keyword'] != "*") {
                    $customer_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->customer_name);
                }
                
                // add new option
                echo '<li onclick="set_item_customer(\''.$rs->customer_id.'\',\''.$rs->customer_name.'\')">'.$customer_name.'</li>';
            }
        }
    }

    public function contract() {
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }

        $customer_model = $this->model->get('customerModel');
        $bank_model = $this->model->get('bankModel');

        $customers = $customer_model->getCustomer($this->registry->router->param_id);

        $tk = "";
        $banks = $bank_model->getBank($customers->company_bank);
        if ($banks) {
            $tk = $banks->bank_name;
        }

        $info = $this->registry->router->addition;
        
        $arr = explode('@', $info);

        $this->view->data['company'] = strtoupper($customers->company_name);
        $this->view->data['mst'] = $customers->mst;
        $this->view->data['address'] = $customers->company_address;
        $this->view->data['phone'] = $customers->company_phone;
        $this->view->data['fax'] = $customers->company_fax;
        $this->view->data['bank_number'] = $customers->company_bank_number;
        $this->view->data['bank'] = $tk;
        $this->view->data['branch'] = $customers->company_bank_branch;
        $this->view->data['name'] = $customers->company_present;
        $this->view->data['position'] = $customers->company_position;

        $this->view->data['from'] = str_replace('$', ' ', $arr[0]);
        $this->view->data['to'] = str_replace('$', ' ', $arr[1]);
        $this->view->data['contract_date'] = explode('-', $arr[2]);
        $this->view->data['contract_number'] = $arr[3];
        $this->view->data['contract_pay'] = str_replace('$', ' ', $arr[4]);
        $this->view->data['contract_valid'] = str_replace('-', '/', $arr[5]);
                
        $this->view->show('marketing/contract');
    }

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 6 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            $marketing = $this->model->get('marketingModel');
            $warehouse = $this->model->get('warehouseModel');

            $marketing_from = trim($_POST['marketing_from']);
            $marketing_to = trim($_POST['marketing_to']);

            if ($marketing_from == "") {
                $w_d = array(
                    'warehouse_name' => trim($_POST['marketing_from_name']),
                    'start_time' => strtotime(date('d-m-Y')),
                    'end_time' => strtotime(date('d-m-Y',strtotime(date('d-m-Y').' +3 year'))),
                );
                $warehouse->createWarehouse($w_d);
                $marketing_from = $warehouse->getLastWarehouse()->warehouse_id;

                $warehouse->updateWarehouse(array('warehouse_code'=>$marketing_from),array('warehouse_id'=>$marketing_from));
            }
            if ($marketing_to == "") {
                $w_d = array(
                    'warehouse_name' => trim($_POST['marketing_from_name']),
                    'start_time' => strtotime(date('d-m-Y')),
                    'end_time' => strtotime(date('d-m-Y',strtotime(date('d-m-Y').' +3 year'))),
                );
                $warehouse->createWarehouse($w_d);
                $marketing_to = $warehouse->getLastWarehouse()->warehouse_id;

                $warehouse->updateWarehouse(array('warehouse_code'=>$marketing_to),array('warehouse_id'=>$marketing_to));
            }

            $data = array(
                        'marketing_date' => strtotime(trim($_POST['marketing_date'])),
                        'marketing_from' => $marketing_from,
                        'marketing_to' => $marketing_to,
                        'customer' => trim($_POST['customer']),
                        'marketing_ton' => trim($_POST['marketing_ton']),
                        'marketing_charge' => trim(str_replace(',','',$_POST['marketing_charge'])),
                        'commission' => trim(str_replace(',','',$_POST['commission'])),
                        'commission_number' => trim($_POST['commission_number']),
                        'marketing_loan' => trim(str_replace(',','',$_POST['marketing_loan'])),
                        'marketing_start' => strtotime(trim($_POST['marketing_start'])),
                        'marketing_end' => strtotime(trim($_POST['marketing_end'])),
                        'loan_content' => trim($_POST['loan_content']),
                        );


            if ($_POST['yes'] != "") {
                //$data['supplies_update_user'] = $_SESSION['userid_logined'];
                //$data['supplies_update_time'] = time();
                //var_dump($data);
                
                    $marketing->updateMarketing($data,array('marketing_id' => $_POST['yes']));
                    echo "Cập nhật thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|marketing|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
            }
            else{
                $data['marketing_create_user'] = $_SESSION['userid_logined'];
                //$data['staff'] = $_POST['staff'];
                //var_dump($data);
                
                    $marketing->createMarketing($data);
                    echo "Thêm thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$marketing->getLastMarketing()->marketing_id."|marketing|".implode("-",$data)."\n"."\r\n";
                        
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

            $supplies = $this->model->get('suppliesModel');

            $data = array(
                        
                        'approve' => 1,
                        'user_approve' => $_SESSION['userid_logined'],
                        );
          
            $supplies->updateSupplies($data,array('supplies_id' => $_POST['data']));

            date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."approve"."|".$_POST['data']."|supplies|"."\n"."\r\n";
                        
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
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 6 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $marketing = $this->model->get('marketingModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $marketing->deleteMarketing($data);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|marketing|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                return true;
            }
            else{

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|marketing|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

                return $marketing->deleteMarketing($_POST['data']);
            }
            
        }
    }

    
    
}
?>