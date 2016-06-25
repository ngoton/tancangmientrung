<?php

Class presentationModel Extends baseModel {
	protected $table = "presentation";

	public function getAllPresentation($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createPresentation($data) 
    {    
        /*$data = array(
        	'Presentationname' => $data['Presentationname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updatePresentation($data,$id) 
    {    
        if ($this->getPresentationByWhere($id)) {
        	/*$data = array(
	        	'Presentationname' => $data['Presentationname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deletePresentation($id){
    	if ($this->getPresentation($id)) {
    		return $this->delete($this->table,array('presentation_id'=>$id));
    	}
    }
    public function getPresentation($id){
    	return $this->getByID($this->table,$id);
    }
    public function getPresentationByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getLastPresentation(){
        return $this->getLast($this->table);
    }
    public function checkPresentation($id,$presentation_title){
        return $this->query('SELECT * FROM presentation WHERE presentation_id != '.$id.' AND presentation_title = "'.$presentation_title);
    }
}
?>