<?php

Class menuModel Extends baseModel {
	protected $table = "menu";

	public function getAllMenu($data = null) 
    {
        return $this->fetchAll($this->table,$data);
    }
    public function getMenu($id){
        return $this->getByID($this->table,$id);
    }
    public function getMenuByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllMenuByWhere($id){
        return $this->query('SELECT * FROM menu WHERE menu_id != '.$id);
    }
    public function checkMenu($id,$menu_name,$menu_parent){
        return $this->query('SELECT * FROM menu WHERE menu_id != '.$id.' AND menu_name = "'.$menu_name.'" AND menu_parent = '.$menu_parent);
    }
    public function createMenu($data) 
    {    
        return $this->insert($this->table,$data);
    }
    public function updateMenu($data,$where) 
    {    
        if ($this->getMenuByWhere($where)) {
            
            return $this->update($this->table,$data,$where);
        }
        
    }
    public function deleteMenu($id){
        if ($this->getMenu($id)) {
            return $this->delete($this->table,array('menu_id'=>$id));
        }
    }

}
?>