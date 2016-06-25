<?php

Class postModel Extends baseModel {
	protected $table = "post";

	public function getAllPost($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }
    public function getPost($id){
        return $this->getByID($this->table,$id);
    }
    public function getPostByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllPostByWhere($id){
        return $this->query('SELECT * FROM post WHERE post_id != '.$id);
    }
    public function checkPost($id,$post_name,$post_parent){
        return $this->query('SELECT * FROM post WHERE post_id != '.$id.' AND post_name = "'.$post_name.'" AND post_parent = '.$post_parent);
    }
    public function createPost($data) 
    {    
        return $this->insert($this->table,$data);
    }
    public function updatePost($data,$where) 
    {    
        if ($this->getPostByWhere($where)) {
            
            return $this->update($this->table,$data,$where);
        }
        
    }
    public function deletePost($id){
        if ($this->getPost($id)) {
            return $this->delete($this->table,array('post_id'=>$id));
        }
    }

}
?>