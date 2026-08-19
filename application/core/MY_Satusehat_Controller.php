<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/REST_Controller.php';

class MY_Satusehat_Controller extends REST_Controller
{
    protected $reqbody;
    protected $reqbodyjson;

    public function __construct()
    {
        parent::__construct();

        $this->load->model("Modelsatusehat", "mss");

        $this->reqbody     = $this->input->raw_input_stream;
        $this->reqbodyjson = json_decode($this->reqbody, true);

        Satusehat::init();

        self::$oauth = Satusehat::generatedoauth();

        headerbundle();
    }
}