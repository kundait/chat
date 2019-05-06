<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_model extends CI_Model {  
     function __construct() {
        parent::__construct();
    }
  
	function add_chat_message($user_id, $chat_message_content,$receiver){	
		$data = array(
				'user_id'	=> $user_id,
				'read'	=> 0,
				'message'       => $chat_message_content,
				'timestamp'	=> time(),
				'user_receive' => $receiver,
		);
		$this->db->insert('chat_messages', $data);
	}

	function get_chat_messages($chat_id, $last_chat_message_id=0, $receiver=false){	           
		$this->db->where("chat_message_id > $last_chat_message_id AND ((user_id = ".$this->db->escape($chat_id)." AND user_receive = ".$this->db->escape($receiver).") OR (user_receive = ".$this->db->escape($chat_id)." AND user_id = ".$this->db->escape($receiver).")) order by timestamp ASC");
		$query = $this->db->get('chat_messages');
		return array_reverse($query->result_array());
		
	}
	function check_chat_messages($user_id){	           
		$this->db->where("(user_id = ".$this->db->escape($user_id)." OR user_receive = ".$this->db->escape($user_id).") order by timestamp ASC");
		$query = $this->db->get('chat_messages');
		return array_reverse($query->result_array());
		
	}
	function update_chat_messages($user_id, $receiver=false, $last_chat_message_id){
		$this->db->where("user_receive", $user_id);
		$this->db->where("user_id", $receiver);		
		$this->db->where("read",0);
		$this->db->update("chat_messages", array('read' => 1));            
		
	}
	function get_unread_messages($user_id=0){	           
		$this->db->where("user_receive", $user_id);
		$this->db->where("read", 0);
		$this->db->order_by("timestamp", "ASC");
		$query = $this->db->get('chat_messages');
		return array_reverse($query->result_array());		
	}
	function get_unread_message($user_id=0){	           
		$this->db->where("user_receive", $user_id);
		$this->db->where("read", 0);
		$this->db->order_by("timestamp", "DESC");		        
		$query = $this->db->get('chat_messages');
		return array_reverse($query->result_array());		
	}

}