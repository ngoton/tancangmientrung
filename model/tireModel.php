<?php

Class tireModel Extends baseModel {
	protected $table = "tire";

	public function getAllTire($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createTire($data) 
    {    
        /*$data = array(
        	'Tirename' => $data['Tirename'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateTire($data,$id) 
    {    
        if ($this->getTireByWhere($id)) {
        	/*$data = array(
	        	'Tirename' => $data['Tirename'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteTire($id){
    	if ($this->getTire($id)) {
    		return $this->delete($this->table,array('tire_id'=>$id));
    	}
    }
    public function getTire($id){
    	return $this->getByID($this->table,$id);
    }
    public function getTireByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllTireByWhere($id){
        return $this->query('SELECT * FROM tire WHERE tire_id != '.$id);
    }
    public function getLastTire(){
        return $this->getLast($this->table);
    }
    public function checkTire($id,$tire_serie){
        return $this->query('SELECT * FROM tire WHERE tire_id != '.$id.' AND tire_serie = "'.$tire_serie);
    }
    public function queryTire($sql){
        return $this->query($sql);
    }
}
?>