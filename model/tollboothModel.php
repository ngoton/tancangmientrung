<?php

Class tollboothModel Extends baseModel {
	protected $table = "toll_booth";

	public function getAllTollbooth($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createTollbooth($data) 
    {    
        /*$data = array(
        	'Tollboothname' => $data['Tollboothname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateTollbooth($data,$id) 
    {    
        if ($this->getTollboothByWhere($id)) {
        	/*$data = array(
	        	'Tollboothname' => $data['Tollboothname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteTollbooth($id){
    	if ($this->getTollbooth($id)) {
    		return $this->delete($this->table,array('toll_booth_id'=>$id));
    	}
    }
    public function getTollbooth($id){
    	return $this->getByID($this->table,$id);
    }
    public function getTollboothByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllTollboothByWhere($id){
        return $this->query('SELECT * FROM toll_booth WHERE toll_booth_id != '.$id);
    }
    public function getLastTollbooth(){
        return $this->getLast($this->table);
    }
    public function checkTollbooth($id,$tollbooth_name){
        return $this->query('SELECT * FROM toll_booth WHERE toll_booth_id != '.$id.' AND toll_booth_name = '.$tollbooth_name);
    }
    public function queryTollbooth($sql){
        return $this->query($sql);
    }
}
?>