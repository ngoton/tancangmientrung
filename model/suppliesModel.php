<?php

Class suppliesModel Extends baseModel {
	protected $table = "supplies";

	public function getAllSupplies($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createSupplies($data) 
    {    
        /*$data = array(
        	'Tirename' => $data['Tirename'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateSupplies($data,$id) 
    {    
        if ($this->getSuppliesByWhere($id)) {
        	/*$data = array(
	        	'Suppliesname' => $data['Suppliesname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteSupplies($id){
    	if ($this->getSupplies($id)) {
    		return $this->delete($this->table,array('supplies_id'=>$id));
    	}
    }
    public function getSupplies($id){
    	return $this->getByID($this->table,$id);
    }
    public function getSuppliesByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllSuppliesByWhere($id){
        return $this->query('SELECT * FROM supplies WHERE supplies_id != '.$id);
    }
    public function getLastSupplies(){
        return $this->getLast($this->table);
    }
    
    public function querySupplies($sql){
        return $this->query($sql);
    }
}
?>