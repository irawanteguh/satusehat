<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Satusehat_Patient_Controller extends REST_Controller
{
    protected $reqbody;
    protected $reqbodyjson;
    protected $oauth;

    public function __construct()
    {
        parent::__construct();

        $this->load->model("Modelsatusehat", "mss");

        $this->reqbody     = $this->input->raw_input_stream;
        $this->reqbodyjson = json_decode($this->reqbody, true);

        Satusehat::init();

        $this->oauth = Satusehat::generatedoauth();

        headerpasien();
    }
}