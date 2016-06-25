<?php

Class equipmentModel Extends baseModel {
	protected $table = "equipment";

	public function getAllEquipment($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createEquipment($data) 
    {    
        /*$data = array(
        	'Equipmentname' => $data['Equipmentname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateEquipment($data,$id) 
    {    
        if ($this->getEquipmentByWhere($id)) {
        	/*$data = array(
	        	'Equipmentname' => $data['Equipmentname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteEquipment($id){
    	if ($this->getEquipment($id)) {
    		return $this->delete($this->table,array('equipment_id'=>$id));
    	}
    }
    public function getEquipment($id){
    	return $this->getByID($this->table,$id);
    }
    public function getEquipmentByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllEquipmentByWhere($id){
        return $this->query('SELECT * FROM equipment WHERE equipment_id != '.$id);
    }
    public function getLastEquipment(){
        return $this->getLast($this->table);
    }
    public function checkEquipment($id,$vehicle,$tire_in,$tire_out){
        return $this->query('SELECT * FROM equipment WHERE equipment_id != '.$id.' AND vehicle = "'.$vehicle.' AND tire_in = '.$tire_in.' AND tire_out = '.$tire_out);
    }
}
?>