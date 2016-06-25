<?php

Class videoModel Extends baseModel {
	protected $table = "video";

	public function getAllVideo($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createVideo($data) 
    {    
        /*$data = array(
        	'Videoname' => $data['Videoname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateVideo($data,$id) 
    {    
        if ($this->getVideoByWhere($id)) {
        	/*$data = array(
	        	'Videoname' => $data['Videoname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteVideo($id){
    	if ($this->getVideo($id)) {
    		return $this->delete($this->table,array('video_id'=>$id));
    	}
    }
    public function getVideo($id){
    	return $this->getByID($this->table,$id);
    }
    public function getVideoByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getLastVideo(){
        return $this->getLast($this->table);
    }
    public function checkVideo($id,$video_title){
        return $this->query('SELECT * FROM video WHERE video_id != '.$id.' AND video_title = "'.$video_title);
    }
}
?>