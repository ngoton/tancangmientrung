<?php
Class newwarehouseController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 7 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý tiền bồi dưỡng CN';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'warehouse_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
        }

        

        $warehouse_model = $this->model->get('warehouseModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $tongsodong = count($warehouse_model->getAllWarehouse());
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
            $search = '( warehouse_name LIKE "%'.$keyword.'%" )';
            $data['where'] = $search;
        }
        
        
        
        $this->view->data['warehouses'] = $warehouse_model->getAllWarehouse($data);

        $this->view->data['lastID'] = isset($warehouse_model->getLastWarehouse()->warehouse_id)?$warehouse_model->getLastWarehouse()->warehouse_id:0;
        
        $this->view->show('newwarehouse/index');
    }

    

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            $warehouse = $this->model->get('warehouseModel');
            $data = array(
                        
                        'warehouse_cont' => trim(str_replace(',','',$_POST['warehouse_cont'])),
                        'warehouse_ton' => trim(str_replace(',','',$_POST['warehouse_ton'])),
                        'warehouse_weight' => trim(str_replace(',','',$_POST['warehouse_weight'])),
                        'warehouse_clean' => trim(str_replace(',','',$_POST['warehouse_clean'])),
                        'warehouse_gate' => trim(str_replace(',','',$_POST['warehouse_gate'])),
                        'warehouse_add' => trim(str_replace(',','',$_POST['warehouse_add'])),
                        'start_time' => strtotime($_POST['start_time']),
                        'end_time' => strtotime($_POST['end_time']),
                        'status' => trim($_POST['status']),
                        );

            if ($_POST['yes'] != "") {
                //$data['warehouse_update_user'] = $_SESSION['userid_logined'];
                //$data['warehouse_update_time'] = time();
                //var_dump($data);
                $data['check_new'] = 1;

                $warehouse_d = $warehouse->getWarehouse($_POST['yes']);

                $dm1 = $warehouse->queryWarehouse('SELECT * FROM warehouse WHERE warehouse_name="'.$warehouse_d->warehouse_name.'" AND start_time <= '.$data['start_time'].' AND end_time <= '.$data['end_time'].' AND end_time >= '.$data['start_time'].' ORDER BY end_time ASC LIMIT 1');
                $dm2 = $warehouse->queryWarehouse('SELECT * FROM warehouse WHERE warehouse_name="'.$warehouse_d->warehouse_name.'" AND end_time >= '.$data['end_time'].' AND start_time >= '.$data['start_time'].' AND start_time <= '.$data['end_time'].' ORDER BY end_time ASC LIMIT 1');
                $dm3 = $warehouse->queryWarehouse('SELECT * FROM warehouse WHERE warehouse_name="'.$warehouse_d->warehouse_name.'" AND start_time <= '.$data['start_time'].' AND end_time >= '.$data['end_time'].' ORDER BY end_time ASC LIMIT 1');

                if ($dm3) {
                    foreach ($dm3 as $row) {
                        $d = array(
                            'end_time' => strtotime(date('d-m-Y',strtotime($_POST['start_time'].' -1 day'))),
                            );
                        $warehouse->updateWarehouse($d,array('warehouse_id'=>$row->warehouse_id));

                        $c = array(
                            'warehouse_name' => $row->warehouse_name,
                            'warehouse_cont' => $row->warehouse_cont,
                            'warehouse_ton' => $row->warehouse_ton,
                            'warehouse_weight' => $row->warehouse_weight,
                            'warehouse_clean' => $row->warehouse_clean,
                            'warehouse_gate' => $row->warehouse_gate,
                            'warehouse_add' => $row->warehouse_add,
                            'start_time' => strtotime(date('d-m-Y',strtotime($_POST['end_time'].' +1 day'))),
                            'end_time' => $row->end_time,
                            'status' => $row->status,
                            'warehouse_code' => $row->warehouse_code,
                            'check_new' => 1,
                            );
                        $warehouse->createWarehouse($c);

                        
                    }

                    $data['warehouse_name'] = trim($_POST['warehouse_name']);
                    $data['warehouse_code'] = $warehouse_d->warehouse_code;
                    
                    $warehouse->createWarehouse($data);
                }
                else if ($dm1 || $dm2) {
                    if($dm1){
                        foreach ($dm1 as $row) {
                            $d = array(
                                'end_time' => strtotime(date('d-m-Y',strtotime($_POST['start_time'].' -1 day'))),
                                );
                            $warehouse->updateWarehouse($d,array('warehouse_id'=>$row->warehouse_id));

                            
                        }
                    }
                    if($dm2){
                        foreach ($dm2 as $row) {
                            $d = array(
                                'start_time' => strtotime(date('d-m-Y',strtotime($_POST['end_time'].' +1 day'))),
                                );
                            $warehouse->updateWarehouse($d,array('warehouse_id'=>$row->warehouse_id));

                            
                        }
                    }

                    $data['warehouse_name'] = trim($_POST['warehouse_name']);
                    $data['warehouse_code'] = $warehouse_d->warehouse_code;
                    
                    $warehouse->createWarehouse($data);
                }

                    echo "Thêm thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$warehouse->getLastWarehouse()->warehouse_id."|warehouse|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
            }
            else{
                //$data['warehouse_create_user'] = $_SESSION['userid_logined'];
                //$data['staff'] = $_POST['staff'];
                //var_dump($data);
                $data['warehouse_name'] = trim($_POST['warehouse_name']);
                if ($warehouse->getWarehouseByWhere(array('warehouse_name'=>$_POST['warehouse_name'],'start_time'=>$data['start_time'],'end_time'=>$data['end_time']))) {
                    echo "Bảng định mức này đã tồn tại";
                    return false;
                }
                else{
                    if ($warehouse->getWarehouseByWhere(array('warehouse_name'=>$_POST['warehouse_name']))) {

                        $data['check_new'] = 1;
                        
                        $dm1 = $warehouse->queryWarehouse('SELECT * FROM warehouse WHERE warehouse_name="'.$warehouse_d->warehouse_name.'" AND start_time <= '.$data['start_time'].' AND end_time <= '.$data['end_time'].' AND end_time >= '.$data['start_time'].' ORDER BY end_time ASC LIMIT 1');
                        $dm2 = $warehouse->queryWarehouse('SELECT * FROM warehouse WHERE warehouse_name="'.$warehouse_d->warehouse_name.'" AND end_time >= '.$data['end_time'].' AND start_time >= '.$data['start_time'].' AND start_time <= '.$data['end_time'].' ORDER BY end_time ASC LIMIT 1');
                        $dm3 = $warehouse->queryWarehouse('SELECT * FROM warehouse WHERE warehouse_name="'.$warehouse_d->warehouse_name.'" AND start_time <= '.$data['start_time'].' AND end_time >= '.$data['end_time'].' ORDER BY end_time ASC LIMIT 1');

                        if ($dm3) {
                            foreach ($dm3 as $row) {
                                $d = array(
                                    'end_time' => strtotime(date('d-m-Y',strtotime($_POST['start_time'].' -1 day'))),
                                    );
                                $warehouse->updateWarehouse($d,array('warehouse_id'=>$row->warehouse_id));

                                $c = array(
                                    'warehouse_name' => $row->warehouse_name,
                                    'warehouse_cont' => $row->warehouse_cont,
                                    'warehouse_ton' => $row->warehouse_ton,
                                    'warehouse_weight' => $row->warehouse_weight,
                                    'warehouse_clean' => $row->warehouse_clean,
                                    'warehouse_gate' => $row->warehouse_gate,
                                    'warehouse_add' => $row->warehouse_add,
                                    'start_time' => strtotime(date('d-m-Y',strtotime($_POST['end_time'].' +1 day'))),
                                    'end_time' => $row->end_time,
                                    'status' => $row->status,
                                    'warehouse_code' => $row->warehouse_code,
                                    'check_new' => 1,
                                    );
                                $warehouse->createWarehouse($c);

                                
                            }

                            
                            $data['warehouse_code'] = $warehouse->getWarehouseByWhere(array('warehouse_name'=>$_POST['warehouse_name']))->warehouse_code;
                            
                            $warehouse->createWarehouse($data);
                        }
                        else if ($dm1 || $dm2) {
                            if($dm1){
                                foreach ($dm1 as $row) {
                                    $d = array(
                                        'end_time' => strtotime(date('d-m-Y',strtotime($_POST['start_time'].' -1 day'))),
                                        );
                                    $warehouse->updateWarehouse($d,array('warehouse_id'=>$row->warehouse_id));

                                    
                                }
                            }
                            if($dm2){
                                foreach ($dm2 as $row) {
                                    $d = array(
                                        'start_time' => strtotime(date('d-m-Y',strtotime($_POST['end_time'].' +1 day'))),
                                        );
                                    $warehouse->updateWarehouse($d,array('warehouse_id'=>$row->warehouse_id));

                                    
                                }
                            }

                            $data['warehouse_code'] = $warehouse->getWarehouseByWhere(array('warehouse_name'=>$_POST['warehouse_name']))->warehouse_code;
                            
                            $warehouse->createWarehouse($data);
                        }
                    }
                    else{
                        $data['warehouse_code'] = $warehouse->getLastWarehouse()->warehouse_id+1;

                        $warehouse->createWarehouse($data);
                    }

                    echo "Thêm thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$warehouse->getLastWarehouse()->warehouse_id."|warehouse|".implode("-",$data)."\n"."\r\n";
                        
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
        if ($_SESSION['role_logined'] != 1 && $_SESSION['role_logined'] != 3 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $warehouse = $this->model->get('warehouseModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $warehouse->deleteWarehouse($data);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|warehouse|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                return true;
            }
            else{

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|warehouse|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

                return $warehouse->deleteWarehouse($_POST['data']);
            }
            
        }
    }

}
?>