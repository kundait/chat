<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Chat extends CI_Controller {
    function __construct() {
        parent::__construct();
        #session library autoloaded in config/autoload.php
        //session_start();        
        if (!isset($_SESSION['username'])) {
            redirect('admin');
        }
        $this->load->model('Chat_model');
        $this->load->model('Admin_model');
    }
 
	public function index(){            
            $this->view_data['user_id']=$_SESSION['username'];            
            $this->load->view('users_list', $this->view_data);               
	}
        
        public function view_chats(){              
            $this->view_data['user_id']=$_SESSION['username'];
            $receiver_id= $this->input->post('receiver_id');              
            $this->session->set_userdata('last_chat_message_id'.$this->view_data['user_id'], 0);
            $this->session->set_userdata('receiver', "$receiver_id");               
            $this->view_data['receiver_id']=$receiver_id;
            $this->load->view('chat', $this->view_data);               
	}
        
        public function ajax_add_chat_message(){ 
            $user_id= $this->input->post('user_id');
            $receiver= $this->input->post('receiver_id');
            $chat_message_content= $this->input->post('chat_message_content');             
            $this->Chat_model->add_chat_message($user_id, $chat_message_content,$receiver);
        }
        
        public function ajax_get_chat_messages(){
            $chat_id= $this->input->post('user_id');
            $last_chat_message_id=(int)$this->session->userdata('last_chat_message_id'.$chat_id);            
            $receiver=$this->session->userdata('receiver');            
            $chat_messages=$this->Chat_model->get_chat_messages($chat_id, $last_chat_message_id, $receiver);      
            if (count($chat_messages) > 0){               
                $last_chat_message_id = $chat_messages[0]['chat_message_id'];
                $this->session->set_userdata('last_chat_message_id'.$chat_id, $last_chat_message_id);
                $chat_messages_html ='<ul>';
                for($x=count($chat_messages); $x>=0; $x--){
                    if (isset( $chat_messages[$x]['message'])){
                        if($chat_messages[$x]['user_receive'] == $chat_id){                           
                            $this->Chat_model->update_chat_messages($chat_messages[$x]['user_receive'], $receiver, $last_chat_message_id);
                        }
                        if($chat_messages[$x]['user_id'] == $_SESSION['username']){
                            $chat_messages_html.="<li><span class='chat_message_header' >".gmdate("Y-m-d H:i:s", $chat_messages[$x]['timestamp'])." by Me:</span><p style='color:purple'>".$chat_messages[$x]['message']."</p></li>";   
                        }else{
                            $chat_messages_html.="<li><span class='chat_message_header'>".gmdate("Y-m-d H:i:s", $chat_messages[$x]['timestamp'])." by ".$chat_messages[$x]['user_id'].":</span><p>".$chat_messages[$x]['message']."</p></li>";
                        }

                    }
                }                
                $chat_messages_html .='<ul>';
                $result=array('status' => 'ok', 'content'=>$chat_messages_html);            
                echo json_encode($result);         
            } 
        }
        
        public function ajax_get_user_list(){
            $has_history=array();
            $this->view_data['user_id']=$_SESSION['username'];
            $all_users=$this->Admin_model->get_users($this->view_data['user_id']);
            $unread_messages=$this->Chat_model->get_unread_message($this->view_data['user_id']);    
            $chat_hist=$this->Chat_model->get_chat_messages($this->view_data['user_id']);           
            if (count($chat_hist) > 0){ 
                for($x=count($chat_hist); $x>=0; $x--){
                    if (isset( $chat_hist[$x]['user_id'])){
                        $has_history[$chat_hist[$x]['user_id']] = $chat_hist[$x]['user_id'];
                        $has_history[$chat_hist[$x]['user_receive']] = $chat_hist[$x]['user_receive'];

                    }elseif(isset( $chat_hist[$x]['user_receive'])){
                        $has_history[$chat_hist[$x]['user_id']] = $chat_hist[$x]['user_id'];
                        $has_history[$chat_hist[$x]['user_receive']] = $chat_hist[$x]['user_receive'];
                    }
                }
            }          
            $has_history=$has_history;
            $unread_messages=$unread_messages;            
            $all_user_html="";
            $all_user_html .="<h3>Users list</h3>";
            $num_of=array();
            
            if (count($all_users) > 0){
                for($x=0; $x<=count($all_users); $x++){
                    if (isset( $all_users[$x]['email_address'])){
                        $v=0;
                        for($c=0; $c<count($unread_messages); $c++){
                            if (isset($unread_messages[$c]['user_id'])){
                                if($all_users[$x]['email_address'] == $unread_messages[$c]['user_id']){                                    
                                    $v++;
                                    $num_of[$unread_messages[$c]['user_id']]=$v;
                                }
                            }
                        }  
                        $all_user_html .= form_open('Chat/view_chats');
                        $all_user_html .="<p><span>".$all_users[$x]['email_address']."</span>";
                        if(isset($num_of[$all_users[$x]['email_address']])){
                            $all_user_html .="<input type=\"text\" value=\"".$all_users[$x]['email_address']."\" name=\"receiver_id\" hidden/>  |   <span class='chat_message_header' ><strong>Unread messages...(".$num_of[$all_users[$x]['email_address']].")</strong></span></p>";
                        }else{
                            $all_user_html .="<input type=\"text\" value=\"".$all_users[$x]['email_address']."\" name=\"receiver_id\" hidden/>  |   <span class='chat_message_header' >No unread messages</span></p>";
                        }
                        $all_user_html2="";
                        $all_user_html3="";
                        for($c=0; $c<=count($unread_messages); $c++){
                            if (isset($unread_messages[$c]['user_id'])){
                                if(($all_users[$x]['email_address'] == $unread_messages[$c]['user_id']) && ($unread_messages[$c]['user_receive'] == $this->view_data['user_id']) ){
                                    $all_user_html2 ="<span class='chat_message_header' > at ".gmdate("Y-m-d H:i:s", $unread_messages[$c]['timestamp'])."</span>";
                                    $all_user_html3 ="<p style='color: blue;'>".$unread_messages[$c]['message']."</p>";
                                }
                            }
                        }
                        $all_user_html .=$all_user_html2;
                        $all_user_html .=$all_user_html3;
                        if (array_key_exists($all_users[$x]['email_address'], $has_history)){
                            $all_user_html .= form_submit('submit:', 'Continue Chating');
                        }else{
                            $all_user_html .= form_submit('submit:', 'Start chating');   
                        }
                        $all_user_html .= form_close();
                        $all_user_html .= "<hr>";
                    }                            
                }                 
            }else{
                $all_user_html .="<p> No active users available yet</p>";
            }
            $result=array('status' => 'ok', 'content'=>$all_user_html);            
            echo json_encode($result);
        }
}
