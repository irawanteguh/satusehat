<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    date_default_timezone_set('Asia/Jakarta');
    use Restserver\Libraries\REST_Controller;
    require APPPATH . '/libraries/REST_Controller.php';

    class OAuth extends REST_Controller{

        public function __construct(){
            parent::__construct();
            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);
            Satusehat::init();
        }

        public function generatedoauth_post(){
            $response = Satusehat::generatedoauth();
            $this->response($response,REST_Controller::HTTP_OK);
        }
        
    }

?>