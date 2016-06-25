<?php
Class taskController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý công việc';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $staff = isset($_POST['staff']) ? $_POST['staff'] : null;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
            $trangthai = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'task_id';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 20;
            $staff = "";
            $batdau = date('d-m-Y');
            $ketthuc = date('d-m-Y', time()+86400); 
            $trangthai = "";
        }

        $user_model = $this->model->get('userModel');
        $users = $user_model->getAllUser();

        $this->view->data['staffs'] = $users;

        $join = array('table'=>'work, user','where'=>'work.work_id = task.work AND user.user_id = task.assigned');

        $task_model = $this->model->get('taskModel');
        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;

        $data = array(
            'where' => 'work_date >= '.strtotime($batdau).' AND work_date <= '.strtotime($ketthuc),
            );
        if($staff != ""){
            $data['where'] = $data['where'].' AND assigned = '.$staff;
        }
        if($trangthai != ""){
            $data['where'] = $data['where'].' AND status = '.$trangthai;
        }

        if ($_SESSION['role_logined'] != 1) {
            $data['where'] = $data['where'].' AND assigned = '.$_SESSION['userid_logined'];
        }
        
        $tongsodong = count($task_model->getAllTask($data, $join));
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

        $this->view->data['staff'] = $staff;
        $this->view->data['trangthai'] = $trangthai;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => 'work_date >= '.strtotime($batdau).' AND work_date <= '.strtotime($ketthuc),
            );
        if($staff != ""){
            $data['where'] = $data['where'].' AND assigned = '.$staff;
        }
        if($trangthai != ""){
            $data['where'] = $data['where'].' AND status = '.$trangthai;
        }
        if ($keyword != '') {
            $search = '( work_name LIKE "%'.$keyword.'%" OR username LIKE "%'.$keyword.'%")';
            $data['where'] = $data['where']." AND ".$search;
        }
        if ($_SESSION['role_logined'] != 1) {
            $data['where'] = $data['where'].' AND assigned = '.$_SESSION['userid_logined'];
        }
        
        
        $this->view->data['tasks'] = $task_model->getAllTask($data,$join);

        $this->view->data['lastID'] = isset($task_model->getLastTask()->task_id)?$task_model->getLastTask()->task_id:0;
        
        $this->view->show('task/index');
    }

    

    public function add(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {

            $work = isset($_POST['work']) ? $_POST['work'] : null;

            if ($work == '') {
                $work_model = $this->model->get('workModel');
                if (!$work_model->getWorkByWhere(array('work_name'=>trim($work)))) {
                  
                    $data_from = array(
                        'work_name' => trim($work),
                        'time_work' => trim($_POST['time_work']),
                        'frequency' => 0,
                        );
                    $work_model->createWork($data_from);

                    $id_work = $work_model->getLastWork()->work_id;
                }
                else{
                    $id_work = $work_model->getWorkByWhere(array('work_name'=>trim($work)))->work_id;
                }
            }
            else{
                $id_work = trim($_POST['work']);
            }


            $task = $this->model->get('taskModel');
            $data = array(
                        'work_date' => strtotime(trim($_POST['work_date'])),
                        'work' => $id_work,
                        'assigned' => trim($_POST['assigned']),
                        );
            if ($_POST['yes'] != "") {
                //$data['task_update_user'] = $_SESSION['userid_logined'];
                //$data['task_update_time'] = time();
                //var_dump($data);
                if ($task->checkTask($_POST['yes'],trim($_POST['work_date']),trim($_POST['work']),trim($_POST['assigned']))) {
                    echo "Thông tin này đã tồn tại";
                    return false;
                }
                else{
                    $task->updateTask($data,array('task_id' => $_POST['yes']));
                    echo "Cập nhật thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|task|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    }
            }
            else{
                //$data['task_create_user'] = $_SESSION['userid_logined'];
                //$data['staff'] = $_POST['staff'];
                //var_dump($data);
                $data['status'] = 0;

                if ($task->getTaskByWhere(array('work_date'=>trim($_POST['work_date']),'work'=>trim($_POST['work']),'assigned'=>trim($_POST['assigned'])))) {
                    echo "Thông tin này đã tồn tại";
                    return false;
                }
                else{
                    $task->createTask($data);
                    echo "Thêm thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$task->getLastTask()->task_id."|task|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
                    
        }
    }

    public function getwork(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $work_model = $this->model->get('workModel');
            
            if ($_POST['keyword'] == "*") {

                $list = $work_model->getAllWork();
            }
            else{
                $data = array(
                'where'=>'( work_name LIKE "%'.$_POST['keyword'].'%" )',
                );
                $list = $work_model->getAllWork($data);
            }
            
            foreach ($list as $rs) {
                // put in bold the written text
                $work_name = $rs->work_name;
                if ($_POST['keyword'] != "*") {
                    $work_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->work_name);
                }
                
                // add new option
                echo '<li onclick="set_item_work(\''.$rs->work_id.'\',\''.$rs->work_name.'\',\''.$rs->time_work.'\')">'.$work_name.'</li>';
            }
        }
    }
    public function getstaff(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_model = $this->model->get('userModel');
            
            if ($_POST['keyword'] == "*") {

                $list = $user_model->getAllUser();
            }
            else{
                $data = array(
                'where'=>'( username LIKE "%'.$_POST['keyword'].'%" )',
                );
                $list = $user_model->getAlluser($data);
            }
            
            foreach ($list as $rs) {
                // put in bold the written text
                $username = $rs->username;
                if ($_POST['keyword'] != "*") {
                    $username = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->username);
                }
                
                // add new option
                echo '<li onclick="set_item_staff(\''.$rs->user_id.'\',\''.$rs->username.'\')">'.$username.'</li>';
            }
        }
    }
    

    public function delete(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $task = $this->model->get('taskModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $task->deleteTask($data);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|task|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                return true;
            }
            else{

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|task|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

                return $task->deleteTask($_POST['data']);
            }
            
        }
    }

    
    public function import(){
        $this->view->disableLayout();
        header('Content-Type: text/html; charset=utf-8');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SESSION['role_logined'] != 1 ) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_FILES['import']['name'] != null) {

            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $task = $this->model->get('taskModel');
            $work = $this->model->get('workModel');

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
                        $val[] = is_numeric($cell->getCalculatedValue()) ? PHPExcel_Style_NumberFormat::toFormattedString($cell->getCalculatedValue(), 'h:mm:ss') : $cell->getCalculatedValue();
                        //here's my prob..
                        //echo $val;
                    }

                    

                    sscanf($val[4], "%d:%d:%d", $hours, $minutes, $seconds);
                    $val[4] = $hours * 60 + $minutes;

                    //$to_time = gmdate("H:i", $val[4]*60); // 3' => 00:03
                    //var_dump($to_time);die();

                    if ($val[1] != null && $val[4] != null) {

                        $work_date = strtotime(date('d-m-Y',strtotime("-1 day")));

                            if(!$work->getWorkByWhere(array('work_name'=>trim($val[1])))) {
                                $work_data = array(
                                'work_name' => trim($val[1]),
                                'time_work' => trim($val[4]),
                                'frequency' => 0,
                                );
                                $work->createWork($work_data);
                                $id_work = $work->getLastWork()->work_id;
                            }
                            else if($work->getWorkByWhere(array('work_name'=>trim($val[1])))){
                                $id_work = $work->getWorkByWhere(array('work_name'=>trim($val[1])))->work_id;
                                $time_work = $work->getWorkByWhere(array('work_name'=>trim($val[1])))->time_work;
                                if (trim($val[4]) < $time_work) {
                                    $work_data = array(
                                    'time_work' => trim($val[4]),
                                    'frequency' => 0,
                                    );
                                    $work->updateWork($work_data,array('work_id' => $id_work));
                                }
                                
                            }

                            $task_data = array(
                                'work_date' => $work_date,
                                'work' => $id_work,
                                'assigned' => $_SESSION['userid_logined'],
                                'status' => 1,
                                'time_end' => trim($val[4]),
                                );
                                $task->createTask($task_data);


                        
                    }
                    
                    //var_dump($this->getNameDistrict($this->lib->stripUnicode($val[1])));
                    // insert


                }
                //return $this->view->redirect('transport');
            
            return $this->view->redirect('task');
        }
        $this->view->show('task/import');

    }
    

    public function view($id) {
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }

        if (!$id) {
            return $this->view->redirect('task');
        }

        $join = array('table'=>'work, user','where'=>'work.work_id = task.work AND user.user_id = task.assigned');

        $task_model = $this->model->get('taskModel');

        $data = array(
            'task_id' => $id,
        );

        $tasks = $task_model->getAllTask($data,$join);

        $this->view->data['tasks'] = $tasks;


        $this->view->show('task/view');
    }
    public function report() {
        $this->view->disableLayout();
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        
        if ($this->registry->router->order_by != null) {
            $staff = $this->registry->router->param_id;
            $batdau = $this->registry->router->page;
            $ketthuc = $this->registry->router->order_by;
        }
        else{
            $staff = "";
            $batdau = $this->registry->router->param_id;
            $ketthuc = $this->registry->router->page;
        }
        $join = array('table'=>'work, user','where'=>'work.work_id = task.work AND user.user_id = task.assigned');

        $task_model = $this->model->get('taskModel');

        $tasks = $task_model->getAllTask(null,$join);

        

        $data = array(
            'where' => 'work_date >= '.$batdau.' AND work_date <= '.$ketthuc,
        );

        if ($staff != "") {
            $data['where'] .= ' AND assigned = '.$staff;
        }

        $tasks_sub = $task_model->getAllTask($data,$join);

        

        $data = array(
            'where' => 'status = 1',
        );

        $tasks_finish = $task_model->getAllTask($data,$join);
        


        $data = array(
            'where' => 'work_date >= '.$batdau.' AND work_date <= '.$ketthuc.' AND status = 1',
        );

        if ($staff != "") {
            $data['where'] .= ' AND assigned = '.$staff;
        }

        $tasks_sub_finish = $task_model->getAllTask($data,$join);

        $data = array(
            'where' => 'status = 0',
        );

        $tasks_still = $task_model->getAllTask($data,$join);
        


        $data = array(
            'where' => 'work_date >= '.$batdau.' AND work_date <= '.$ketthuc.' AND status = 0',
        );

        if ($staff != "") {
            $data['where'] .= ' AND assigned = '.$staff;
        }

        $tasks_sub_still = $task_model->getAllTask($data,$join);

        $finish_per = round(count($tasks_finish)*100/count($tasks));


        $this->view->data['total'] = count($tasks);
        $this->view->data['total_sub'] = count($tasks_sub);
        $this->view->data['start'] = $this->lib->hien_thi_ngay_thang($batdau);
        $this->view->data['end'] = $this->lib->hien_thi_ngay_thang($ketthuc);
        $this->view->data['tasks_finish'] = count($tasks_finish);
        $this->view->data['tasks_sub_finish'] = count($tasks_sub_finish);
        $this->view->data['tasks_still'] = count($tasks_still);
        $this->view->data['tasks_sub_still'] = count($tasks_sub_still);
        $this->view->data['finish_per'] = $finish_per;


        $this->view->show('task/report');
    }

}
?>