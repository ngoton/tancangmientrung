<?php

Class controlModel Extends baseModel {
	protected $table = "control";

	public function getAllControl($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createControl($data) 
    {    
        /*$data = array(
        	'Controlname' => $data['Controlname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateControl($data,$id) 
    {    
        if ($this->getControlByWhere($id)) {
        	/*$data = array(
	        	'Controlname' => $data['Controlname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteControl($id){
    	if ($this->getControl($id)) {
    		return $this->delete($this->table,array('control_id'=>$id));
    	}
    }
    public function getControl($id){
    	return $this->getByID($this->table,$id);
    }
    public function getControlByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllControlByWhere($id){
        return $this->query('SELECT * FROM control WHERE control_id != '.$id);
    }
    public function getLastControl(){
        return $this->getLast($this->table);
    }
    public function queryControl($sql){
        return $this->query($sql);
    }
}
?>