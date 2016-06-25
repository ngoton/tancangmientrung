<?php

Class brochureModel Extends baseModel {
	protected $table = "brochure";

	public function getAllBrochure($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createBrochure($data) 
    {    
        /*$data = array(
        	'Brochurename' => $data['Brochurename'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateBrochure($data,$id) 
    {    
        if ($this->getBrochureByWhere($id)) {
        	/*$data = array(
	        	'Brochurename' => $data['Brochurename'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteBrochure($id){
    	if ($this->getBrochure($id)) {
    		return $this->delete($this->table,array('brochure_id'=>$id));
    	}
    }
    public function getBrochure($id){
    	return $this->getByID($this->table,$id);
    }
    public function getBrochureByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getLastBrochure(){
        return $this->getLast($this->table);
    }
    public function checkBrochure($id,$brochure_title){
        return $this->query('SELECT * FROM brochure WHERE brochure_id != '.$id.' AND brochure_title = "'.$brochure_title);
    }
}
?>