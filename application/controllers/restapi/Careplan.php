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

    class Careplan extends REST_Controller{
        public static $oauth;

        public function __construct(){
            parent::__construct();
            $this->load->model("Modelcareplan", "md");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
            headerbundle();
        }
        

        public function careplan_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->careplan(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor = "";
                        $statusMsg   = "";
                        $pasienid    = "";
                        $episodeid   = "";
                        $poliid      = "";
                        $dokterid    = "";
                        $identifier  = "";

                        $body                       = [];
                        $careplan                   = [];
                        $careplanresource           = [];
                        $careplanresourcecoding     = [];
                        $careplanresourceidentifier = [];

                        $pasienid   = $a->PASIEN_ID;
                        $episodeid  = $a->EPISODE_ID;
                        $poliid     = $a->POLI_ID;
                        $dokterid   = $a->DOKTER_ID;
                        $identifier = $a->TRANS_SOAP;

                        if(SERVER === "production"){
                            $patientid        = $a->PATIENTID;
                            $mrpas            = $a->PATIENTMR;
                            $patientname      = $a->PATIENTNAME;
                            $practitionerid   = $a->PRACTITIONERID;
                            $practitionername = $a->PRACTITIONERNAME;
                        }else{
                            $resultgetRandomPatient      = Satusehat::getRandomPatient();
                            $resultgetRandomPractitioner = Satusehat::getRandomPractitioner();
                            $patientid                   = $resultgetRandomPatient['ihs'];
                            $mrpas                       = "123456";
                            $patientname                 = $resultgetRandomPatient['nama'];
                            $practitionerid              = $resultgetRandomPractitioner['ihs'];
                            $practitionername            = $resultgetRandomPractitioner['nama'];
                        }

                        $careplanresourcecoding['code']    = "736271009";
                        $careplanresourcecoding['display'] = "Outpatient care plan";
                        $careplanresourcecoding['system']  = "http://snomed.info/sct";

                        $careplanresourceidentifier['system']   = "http://sys-ids.kemkes.go.id/CarePlan/".RS_ID;
                        $careplanresourceidentifier['use']      = "official";
                        $careplanresourceidentifier['value']    = $identifier;

                        $careplanresource['resourceType']           = "CarePlan";
                        $careplanresource['status']                 = "active";
                        $careplanresource['intent']                 = "plan";
                        $careplanresource['description']            = $a->P;
                        $careplanresource['subject']['reference']   = "Patient/".$patientid;
                        $careplanresource['subject']['display']     = $patientname;
                        $careplanresource['encounter']['reference'] = "Encounter/".$a->RESOURCEID;
                        $careplanresource['created']                = $a->CREATED_DATE;
                        $careplanresource['author']['reference']    = "Practitioner/".$practitionerid;
                        $careplanresource['title']                  = "Rencana Rawat Pasien";
                        $careplanresource['category'][]['coding'][] = $careplanresourcecoding;
                        $careplanresource['identifier'][] = $careplanresourceidentifier;
                        

                        $careplan['fullUrl']           = "urn:uuid:".Satusehat::uuid();
                        $careplan['request']['method'] = "POST";
                        $careplan['request']['url']    = "CarePlan";
                        $careplan['resource']          = $careplanresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $careplan;

                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);

                        if(isset($response['entry'])){
                            foreach($response['entry'] as $entrys){
                                $simpanlog = [];

                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['DOKTER_ID']     = $dokterid;
                                $simpanlog['IDENTIFIER']    = $identifier;
                                $simpanlog['TRANS_CO']      = $identifier;
                                $simpanlog['LOCATION']      = $entrys['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $entrys['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $entrys['response']['resourceID'];
                                $simpanlog['ETAG']          = $entrys['response']['etag'];
                                $simpanlog['STATUS']        = $entrys['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $entrys['response']['lastModified'];
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                $simpanlog['JENIS']         = "1";

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
                                echo formatlogbundle($pasienid,$episodeid,'CarePlan','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'CarePlan','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'CarePlan','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetobservation = [];
                                            $responsegetobservation = Satusehat::getdata("CarePlan","identifier",$identifier,self::$oauth['access_token']);

                                            if(isset($responsegetobservation['entry'])){
                                                foreach($responsegetobservation['entry'] as $responsegetobservations){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['IDENTIFIER']    = $identifier;
                                                    $simpanlog['TRANS_CO']      = $identifier;
                                                    $simpanlog['LOCATION']      = $responsegetobservations['fullUrl']."/_history/".trim($responsegetobservations['resource']['meta']['versionId'], 'W/"');
                                                    $simpanlog['RESOURCE_TYPE'] = $responsegetobservations['resource']['resourceType'];
                                                    $simpanlog['RESOURCE_ID']   = $responsegetobservations['resource']['id'];
                                                    $simpanlog['ETAG']          = 'W/"' . $responsegetobservations['resource']['meta']['versionId'] . '"';
                                                    $simpanlog['STATUS']        = "201 Created";
                                                    $simpanlog['LAST_MODIFIED'] = $responsegetobservations['resource']['meta']['lastUpdated'];
                                                    $simpanlog['ENVIRONMENT']   = SERVER;
                                                    $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                    $simpanlog['JENIS']         = "1";
                                                    
                                                    $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id']);

                                                    if(empty($resultcekdataresouce)){
                                                        $this->mss->insertdata($simpanlog);
                                                    }

                                                    $statusColor = "yellow";
                                                    $statusMsg   = "GET CarePlan BY IDENTIFIER";
                                                    echo formatlogbundle($pasienid,$episodeid,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id'], $statusMsg,$statusColor);
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