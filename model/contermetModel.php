<?php

Class contermetModel Extends baseModel {
	protected $table = "contermet";

	public function getAllContermet($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createContermet($data) 
    {    
        /*$data = array(
        	'Contermetname' => $data['Contermetname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateContermet($data,$id) 
    {    
        if ($this->getContermetByWhere($id)) {
        	/*$data = array(
	        	'Contermetname' => $data['Contermetname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteContermet($id){
    	if ($this->getContermet($id)) {
    		return $this->delete($this->table,array('contermet_id'=>$id));
    	}
    }
    public function getContermet($id){
    	return $this->getByID($this->table,$id);
    }
    public function getContermetByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllContermetByWhere($id){
        return $this->query('SELECT * FROM contermet WHERE contermet_id != '.$id);
    }
    public function getLastContermet(){
        return $this->getLast($this->table);
    }
    public function queryContermet($sql){
        return $this->query($sql);
    }
}
?>