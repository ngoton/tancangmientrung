<?php

Class imageModel Extends baseModel {
	protected $table = "image";

	public function getAllImage($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createImage($data) 
    {    
        /*$data = array(
        	'Imagename' => $data['Imagename'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateImage($data,$id) 
    {    
        if ($this->getImageByWhere($id)) {
        	/*$data = array(
	        	'Imagename' => $data['Imagename'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteImage($id){
    	if ($this->getImage($id)) {
    		return $this->delete($this->table,array('image_id'=>$id));
    	}
    }
    public function getImage($id){
    	return $this->getByID($this->table,$id);
    }
    public function getImageByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getLastImage(){
        return $this->getLast($this->table);
    }
}
?>