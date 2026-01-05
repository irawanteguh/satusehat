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

    class Procedure extends REST_Controller{
        public static $oauth;

        public function __construct(){
            parent::__construct();
            $this->load->model("Modelprocedure", "md");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
            headerbundle();
        }
        
        public function radiologi_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->radiologi(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor         = "";
                        $statusMsg           = "";
                        $patientid           = "";
                        $mrpas               = "";
                        $patientname         = "";
                        $practitionerid      = "";
                        $practitionername    = "";
                        $practitionerradid   = "";
                        $practitionerradname = "";
                        $locationid          = "";
                        $locationname        = "";
                        $pasienid            = "";
                        $episodeid           = "";
                        $poliid              = "";
                        $transco             = "";
                        $layanid             = "";
                        $dokterid            = "";
                        $identifier          = "";

                        $body                            = [];
                        $procedure                       = [];
                        $procedureresourcecategory       = [];
                        $procedureresourcecategorycoding = [];
                        $procedureresourcecode           = [];
                        $procedureresourceperformer      = [];

                        $pasienid   = $a->PASIEN_ID;
                        $episodeid  = $a->EPISODE_ID;
                        $poliid     = $a->POLI_ID;
                        $transco    = $a->TRANS_CO;
                        $layanid    = $a->TEST_ID;
                        $dokterid   = $a->DOKTER_ID;
                        $identifier = $a->TRANS_CO."-".$a->TEST_ID;

                        if(SERVER === "production"){
                            $patientid           = $a->PATIENTID;
                            $mrpas               = $a->PATIENTMR;
                            $patientname         = $a->PATIENTNAME;
                            $practitionerid      = $a->PRACTITIONERID;
                            $practitionername    = $a->PRACTITIONERNAME;
                            $practitionerradid   = $a->PRACTITIONERRADID;
                            $practitionerradname = $a->PRACTITIONERRADNAME;
                            $locationid          = $a->LOCATIONID;
                            $locationname        = $a->LOCATIONNAME;
                        }else{
                            $resultgetRandomPatient      = Satusehat::getRandomPatient();
                            $resultgetRandomPractitioner = Satusehat::getRandomPractitioner();

                            $patientid           = $resultgetRandomPatient['ihs'];
                            $mrpas               = "123456";
                            $patientname         = $resultgetRandomPatient['nama'];
                            $practitionerid      = $resultgetRandomPractitioner['ihs'];
                            $practitionername    = $resultgetRandomPractitioner['nama'];
                            $practitionerradid   = $resultgetRandomPractitioner['ihs'];
                            $practitionerradname = $resultgetRandomPractitioner['nama'];
                            $locationid          = "91b6b664-929e-4b67-802a-a0a86a607a0c";
                            $locationname        = $a->LOCATIONNAME;
                        }

                        $procedureresourcecategorycoding['system']      = "http://snomed.info/sct";
                        $procedureresourcecategorycoding['code']        = "103693007";
                        $procedureresourcecategorycoding['display']     = "Diagnostic procedure";
                        
                        $procedureresourcecategory['coding'][]            = $procedureresourcecategorycoding;
                        $procedureresourcecode['system']                  = "http://loinc.org";
                        $procedureresourcecode['code']                    = $a->LOINCCODE;
                        $procedureresourcecode['display']                 = $a->LOINCDESC;
                        $procedureresourceperformer['actor']['reference'] = "Practitioner/".$practitionerradid;
                        $procedureresourceperformer['actor']['display']   = $practitionerradname;

                        $procedureresource['category']                 = $procedureresourcecategory;
                        $procedureresource['code']['coding'][]         = $procedureresourcecode;
                        $procedureresource['encounter']['reference']   = "Encounter/".$a->RESOURCE_ID;
                        $procedureresource['note'][]['text']           = (isset($a->CATATAN) && trim($a->CATATAN) !== '') ? $a->CATATAN : '-';
                        // $procedureresource['performedPeriod']['end']   = $a->TGLORDER;
                        // $procedureresource['performedPeriod']['start'] = $a->TGLORDER;
                        $procedureresource['performer'][]              = $procedureresourceperformer;
                        $procedureresource['resourceType']             = "Procedure";
                        $procedureresource['status']                   = "completed";
                        $procedureresource['subject']['reference']     = "Patient/".$patientid;
                        $procedureresource['subject']['display']       = $patientname;
                        
                        
                        $procedure['fullUrl']           = "urn:uuid:".Satusehat::uuid();
                        $procedure['request']['method'] = "POST";
                        $procedure['request']['url']    = "Procedure";
                        $procedure['resource']          = $procedureresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $procedure;

                        // $this->response($procedureresource);

                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);

                        if(isset($response['entry'])){
                            foreach($response['entry'] as $entrys){
                                $simpanlog = [];

                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['DOKTER_ID']     = $dokterid;
                                $simpanlog['TRANS_CO']      = $transco;
                                $simpanlog['LAYAN_ID']      = $layanid;
                                $simpanlog['IDENTIFIER']    = $identifier;
                                $simpanlog['LOCATION']      = $entrys['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $entrys['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $entrys['response']['resourceID'];
                                $simpanlog['ETAG']          = $entrys['response']['etag'];
                                $simpanlog['STATUS']        = $entrys['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $entrys['response']['lastModified'];
                                $simpanlog['JENIS']         = "1";
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
    
                                $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$entrys['response']['resourceType'],$entrys['response']['resourceID']);
                               
                                if(empty($resultcekdataresouce)){
                                    $this->mss->insertdata($simpanlog);
                                }
                                

                                $statusColor = "green";
                                $statusMsg   = "Success";
                                echo formatlogbundle($pasienid,$episodeid,$entrys['response']['resourceType'],$entrys['response']['resourceID'], $statusMsg,$statusColor);
                            } 
                        }else{
                            if($response === null){
                                $statusColor = "red";
                                echo formatlogbundle($pasienid,$episodeid,'Procedure','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'Procedure','',$statusMsg,$statusColor);
                                }else{
                                    if(isset($response['issue']) && is_array($response['issue']) && count($response['issue']) > 0){
                                        
                                        foreach ($response['issue'] as $issues) {
                                            if($issues['code']!="duplicate"){
                                                $statusColor = "";
                                                $severity    = $issues['severity'] ?? 'unknown';
                                                $code        = $issues['code'] ?? '-';
                                                $text        = $issues['details']['text'] ?? '-';
                                                $diagnostics = $issues['diagnostics'] ?? $text;
                                                $expression  = $issues['expression'][0] ?? '-';

                                                switch ($severity) {
                                                    case 'error':
                                                        $statusColor = 'red';
                                                        break;
                                                    case 'warning':
                                                        $statusColor = 'yellow';
                                                        break;
                                                    default:
                                                        $statusColor = 'white';
                                                }

                                                $statusMsg = strtoupper($severity).' | '.$diagnostics.' | '.$expression;
                                                echo formatlogbundle($pasienid,$episodeid,'Procedure','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetServiceRequest = [];
                                            $parameter                 = "http://sys-ids.kemkes.go.id/acsn/".RS_ID."|".$acsn;
                                            $responsegetServiceRequest = Satusehat::getdata("Procedure","identifier",$parameter,self::$oauth['access_token']);

                                            if(isset($responsegetServiceRequest['entry'])){
                                                foreach($responsegetServiceRequest['entry'] as $responsegetServiceRequests){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['TRANS_CO']      = $transco;
                                                    $simpanlog['LAYAN_ID']      = $layanid;
                                                    $simpanlog['IDENTIFIER']    = $identifier;
                                                    $simpanlog['LOCATION']      = $responsegetServiceRequests['fullUrl']."/_history/".trim($responsegetServiceRequests['resource']['meta']['versionId'], 'W/"');
                                                    $simpanlog['RESOURCE_TYPE'] = $responsegetServiceRequests['resource']['resourceType'];
                                                    $simpanlog['RESOURCE_ID']   = $responsegetServiceRequests['resource']['id'];
                                                    $simpanlog['ETAG']          = 'W/"' . $responsegetServiceRequests['resource']['meta']['versionId'] . '"';
                                                    $simpanlog['STATUS']        = "201 Created";
                                                    $simpanlog['LAST_MODIFIED'] = $responsegetServiceRequests['resource']['meta']['lastUpdated'];
                                                    $simpanlog['ENVIRONMENT']   = SERVER;
                                                    $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                    $simpanlog['JENIS']         = "1";
                                                    
                                                    $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetServiceRequests['resource']['resourceType'],$responsegetServiceRequests['resource']['id']);
                                                    if(empty($resultcekdataresouce)){
                                                        $this->mss->insertdata($simpanlog);
                                                    }

                                                    $statusColor = "yellow";
                                                    $statusMsg   = "GET Procedure BY ACSN";
                                                    echo formatlogbundle($pasienid,$episodeid,$responsegetServiceRequests['resource']['resourceType'],$responsegetServiceRequests['resource']['id'], $statusMsg,$statusColor);
                                                }
                                            }
                                        }
                                    }
                                }

                                
                            }
                        }
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