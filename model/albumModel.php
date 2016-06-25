<?php

Class albumModel Extends baseModel {
	protected $table = "album";

	public function getAllAlbum($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createAlbum($data) 
    {    
        /*$data = array(
        	'Albumname' => $data['Albumname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateAlbum($data,$id) 
    {    
        if ($this->getAlbumByWhere($id)) {
        	/*$data = array(
	        	'Albumname' => $data['Albumname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteAlbum($id){
    	if ($this->getAlbum($id)) {
    		return $this->delete($this->table,array('album_id'=>$id));
    	}
    }
    public function getAlbum($id){
    	return $this->getByID($this->table,$id);
    }
    public function getAlbumByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getLastAlbum(){
        return $this->getLast($this->table);
    }
    public function checkAlbum($id,$album_name){
        return $this->query('SELECT * FROM album WHERE album_id != '.$id.' AND album_name = "'.$album_name);
    }
}
?>