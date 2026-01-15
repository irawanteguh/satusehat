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

    class Medication extends REST_Controller{
        public static $oauth;

        public function __construct(){
            parent::__construct();
            $this->load->model("Modelmedicationrequest", "md");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
            headerbundle();
        }
        
        public function singledose_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->medicationsingledose(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor = "";
                        $statusMsg   = "";
                        $pasienid    = "";
                        $episodeid   = "";
                        $poliid      = "";
                        $dokterid    = "";
                        $obatid      = "";
                        $transco     = "";
                        $identifier  = "";

                        $body                                           = [];
                        $medication                                     = [];
                        $medicationresource                             = [];
                        $medicationresourcecode                         = [];
                        $medicationresourcextension                     = [];
                        $medicationresourcextensionvalueCodeableConcept = [];
                        $medicationresourceidentifier                   = [];


                        $pasienid   = $a->PASIEN_ID;
                        $episodeid  = $a->EPISODE_ID;
                        $poliid     = $a->POLI_ID;
                        $dokterid   = $a->DOKTER_ID;
                        $obatid     = $a->OBAT_ID;
                        $transco    = $a->TRANS_CO;
                        $identifier = $a->EPISODE_ID."-".$a->TRANS_CO."-".$a->OBAT_ID;

                        $medicationresourcextensionvalueCodeableConcept['code']    = "NC";
                        $medicationresourcextensionvalueCodeableConcept['display'] = "Non-compound";
                        $medicationresourcextensionvalueCodeableConcept['system']  = "http://terminology.kemkes.go.id/CodeSystem/medication-type";

                        $medicationresourcecode['code']                                 = $a->KFAID;
                        $medicationresourcecode['display']                              = $a->POANAME;
                        $medicationresourcecode['system']                               = "http://sys-ids.kemkes.go.id/kfa";
                        $medicationresourcextension['url']                              = "https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType";
                        $medicationresourcextension['valueCodeableConcept']['coding'][] = $medicationresourcextensionvalueCodeableConcept;
                        $medicationresourceidentifier['system']                        = "http://sys-ids.kemkes.go.id/medication/".RS_ID;
                        $medicationresourceidentifier['use']                           = "official";
                        $medicationresourceidentifier['value']                         = $identifier;
                        
                        $medicationresource['code']['coding'][] = $medicationresourcecode;
                        $medicationresource['extension'][]      = $medicationresourcextension;
                        $medicationresource['identifier'][]     = $medicationresourceidentifier;
                        $medicationresource['resourceType']     = "Medication";
                        
                        $medication['fullUrl']           = "urn:uuid:".Satusehat::uuid();
                        $medication['request']['method'] = "POST";
                        $medication['request']['url']    = "Medication";
                        $medication['resource']          = $medicationresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $medication;
                        
                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);

                        if(isset($response['entry'])){
                            foreach($response['entry'] as $entrys){
                                $simpanlog = [];

                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['DOKTER_ID']     = $dokterid;
                                $simpanlog['IDENTIFIER']    = $identifier;
                                $simpanlog['OBAT_ID']       = $obatid;
                                $simpanlog['TRANS_CO']      = $transco;
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
                                echo formatlogbundle($pasienid,$episodeid,'Medication','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'Medication','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'Medication','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetServiceRequest = [];
                                            $responsegetServiceRequest = Satusehat::getdata("Medication","identifier",$identifier,self::$oauth['access_token']);

                                            if(isset($responsegetServiceRequest['entry'])){
                                                foreach($responsegetServiceRequest['entry'] as $responsegetServiceRequests){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['IDENTIFIER']    = $identifier;
                                                    $simpanlog['OBAT_ID']       = $obatid;
                                                    $simpanlog['TRANS_CO']      = $transco;
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
                                                    $statusMsg   = "GET Medication BY Identifier";
                                                    echo formatlogbundle($pasienid,$episodeid,$responsegetServiceRequests['resource']['resourceType'],$responsegetServiceRequests['resource']['id'], $statusMsg,$statusColor);
                                                }
                                            }else{
                                                $statusColor = "yellow";
                                                $statusMsg   = "GET Medication BY Identifier Tidak Di Temukan";
                                                echo formatlogbundle($pasienid,$episodeid,"Medication","", $statusMsg,$statusColor);
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