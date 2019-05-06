<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Admin extends CI_Controller {
    
    function __construct() {
        parent::__construct();
        #session library autoloaded in config/autoload.php
    }
 
	public function index(){	
            if (isset($_SESSION['username'])) {
                redirect('Chat');
            }
            $this->load->library('form_validation');
            $this->form_validation->set_rules('email_address', 'Email Address', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[4]');
            if($this->form_validation->run() !== false){
                $this->load->model('Admin_model');
                $res = $this->Admin_model->verify_user($this->input->post('email_address') , $this->input->post('password'));
                if ($res !== false){
                    $_SESSION['username']=$this->input->post('email_address');
                    redirect('Chat');
                }else{
                    $this->session->set_flashdata('failed', 'Invalid username and password');
                }                    
            }		
            $this->load->view('login_view');
	}
        
        public function register_view(){	
            if (isset($_SESSION['username'])) {
                redirect('Chat');
            }
            $this->session->set_flashdata('failed', false);
            $this->load->view('register_view');            
        }
        
        public function register(){	                
            if (isset($_SESSION['username'])) {
                redirect('Chat');
            }
            $this->session->set_flashdata('failed', false);
            $this->load->library('form_validation');
            $this->form_validation->set_rules('email_address', 'Email Address', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[4]');
            $this->form_validation->set_rules('confirm_password', 'Confirm password', 'required|min_length[4]|matches[password]');

            if($this->form_validation->run() !== false){
                $data=array(
                    'username' => $this->input->post('email_address'),
                    'email_address' => $this->input->post('email_address'),
                    'password' => sha1($this->input->post('password'))
                );
                $this->load->model('Admin_model');
                $res = $this->Admin_model->check_user($this->input->post('email_address') , $this->input->post('password'));
                if ($res != false){					
                    $this->session->set_flashdata('failed', 'Username already exits, please use a different email address');
                   // redirect('admin/register');                  
                }else{
                    $this->Admin_model->add_user($data);
                    $this->session->set_flashdata('success', 'You have successfully resgistered, proceed to login');
                    redirect('admin');
                }
            }                
            $this->load->view('register_view');            
        }
        
        public function logout(){	
            session_destroy();
            $this->load->view('login_view');
        }
}