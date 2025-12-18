<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    date_default_timezone_set('Asia/Jakarta');
    use Restserver\Libraries\REST_Controller;
    require APPPATH . '/libraries/REST_Controller.php';

    if(!function_exists('color')){
        function color($name = null){
            $colors = [
                'reset'          => "\033[0m",
                'black'          => "\033[30m",
                'red'            => "\033[31m",
                'green'          => "\033[32m",
                'yellow'         => "\033[33m",
                'blue'           => "\033[34m",
                'magenta'        => "\033[35m",
                'cyan'           => "\033[36m",
                'white'          => "\033[37m",
                'gray'           => "\033[90m",
                'light_red'      => "\033[91m",
                'light_green'    => "\033[92m",
                'light_yellow'   => "\033[93m",
                'light_blue'     => "\033[94m",
                'light_magenta'  => "\033[95m",
                'light_cyan'     => "\033[96m",
                'light_white'    => "\033[97m",
            ];

            return $colors[$name] ?? $colors['reset'];
        }
    }

    class Patient extends REST_Controller{

        public static $oauth;
        
        public function __construct(){
            parent::__construct();
            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);
            $this->load->model("Modelpatient", "md");
            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
        }

        public function headerlog(){
            echo PHP_EOL;
            echo color('cyan').str_pad("NO_KTP", 16).str_pad("SATUSEHAT_ID", 14)."MESSAGE".PHP_EOL;
        }

        public function formatlog($identity, $useridentifier, $message, $colorIdentity = 'cyan', $colorUser = 'yellow', $colorMessage = 'white') {
            $identityWidth       = 50;
            $userIdentifierWidth = 42;

            $colorStartIdentity  = color($colorIdentity);
            $colorStartUser      = color($colorUser);
            $colorStartMessage   = color($colorMessage);
            $reset               = color('reset');

            $formatted  = $colorStartIdentity . str_pad($identity, $identityWidth) . $reset;
            $formatted .= $colorStartUser . str_pad($useridentifier, $userIdentifierWidth) . $reset;
            $formatted .= $colorStartMessage . $message . $reset;

            return $formatted . PHP_EOL;
        }

        public function patientid_post(){
            $this->headerlog();

            if(!isset(self::$oauth['issue'])){
                $finalresponse = [];
                $result        = $this->md->getpatientid();
                foreach ($result as $a){
                    $response = Satusehat::getpatientid($a->NO_IDENTITAS,self::$oauth['access_token']);
                    if(isset($response['entry'][0]['resource']['id'])){
                        $data['SATUSEHAT_ID']=$response['entry'][0]['resource']['id'];
                    }else{
                        $data['SATUSEHAT_ID']="X";
                    }
                    $this->md->updatedatapatient($a->PASIEN_ID,$data);
                    $finalresponse [] = $response;
                }
                $this->response($finalresponse,REST_Controller::HTTP_OK);
            }else{
                // $this->response(self::$oauth,REST_Controller::HTTP_OK);
                echo color('red')."Data Tidak Ditemukan";
            }
        }
    }

?>