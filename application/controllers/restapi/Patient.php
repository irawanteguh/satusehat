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
            $this->load->model("Modelpatient", "md");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
            headerpasien();
        }
        

        public function patientid_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->datapasien();
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor = "";
                        $statusMsg   = "";

                        $pasienid    = "";
                        $noidentitas = "";
                        $namapasien  = "";
                        $concentdate = "";

                        $pasienid    = $a->PASIEN_ID;
                        $noidentitas = $a->NO_IDENTITAS;
                        $namapasien  = $a->NAMA;
                        $concentdate = $a->SATUSEHAT_DATE;

                        $response = Satusehat::getpatientid($noidentitas,self::$oauth['access_token']);
                        if(isset($response['entry'][0]['resource']['id'])){
                            $satusehatid = $response['entry'][0]['resource']['id'];

                            $statusColor = "green";
                            $statusMsg   = "OK";

                            $data['SATUSEHAT_ID']=$satusehatid;
                        }else{
                            $satusehatid = "NOT FOUND";

                            $statusColor = "red";
                            $statusMsg   = "NOT FOUND";

                            $data['SATUSEHAT_ID']=$satusehatid;
                        }

                        $this->md->updatedatapatient($pasienid,$data);
                        echo formatlogpasien($pasienid,$noidentitas,$namapasien,$concentdate,$satusehatid,$statusMsg,$statusColor);
                    }
                }else{
                    echo color('red')."Data Tidak Ditemukan";
                }
            }else{
                echo color('red').self::$oauth['issue'][0]['details']['text'];
            }
        }
    }

?>