<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {  
    function __construct() {
        //parent::__construct();
    }
    
    public function verify_user($email, $password){
        $q = $this->db
            ->where('email_address', $email) 
            ->where('password', sha1($password))
            ->limit(1)               
            ->get('users');       
        if ($q->num_rows() > 0){         
            return $q->row();       
        }else{
            return false;
        }
    }
	public function check_user($email){
        $q = $this->db
            ->where('email_address', $email)            
            ->limit(1)               
            ->get('users');       
        if ($q->num_rows() > 0){         
            return $q->row();       
        }else{
            return false;
        }
    }
    
    public function add_user($data){
        $this->db->insert('users',$data);       
    }
    
    public function get_users($user_id){
        $this->db->where('email_address != ', $user_id);
        $query = $this->db->get('users');
        return array_reverse($query->result_array());
    }
    
    public function get_user($user_id){
        $this->db->where('email_address = ', $user_id);
        $query = $this->db->get('users');
        return array_reverse($query->result_array());
    }
    
}