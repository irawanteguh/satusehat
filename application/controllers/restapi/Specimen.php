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

    class Specimen extends REST_Controller{
        public static $oauth;

        public function __construct(){
            parent::__construct();
            $this->load->model("Modelspecimen", "md");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
            headerbundle();
        }

        public function specimenlab_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->specimentlab(SERVER);

                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor      = "";
                        $statusMsg        = "";
                        $pasienid         = "";
                        $episodeid        = "";
                        $poliid           = "";
                        $dokterid         = "";
                        $layanid          = "";
                        $sampelid         = "";
                        $transco          = "";
                        $uuidspecimentlab = "";

                        $body                                                   = [];
                        $specimentlab                                           = [];
                        $specimentlabresource                                   = [];
                        $specimentlabresourceidentifier                         = [];
                        $specimentlabresourcetype                               = [];
                        $specimentlabresourcecollectionmethod                   = [];
                        $specimentlabresourcecollectionmethodcoding             = [];
                        $specimentlabresourcefastingStatusCodeableConcept       = [];
                        $specimentlabresourcefastingStatusCodeableConceptcoding = [];
                        $specimentlabresourcerequester                          = [];

                        $pasienid         = $a->PASIEN_ID;
                        $episodeid        = $a->EPISODE_ID;
                        $poliid           = $a->POLI_ID;
                        $dokterid         = $a->DOKTER_ID;
                        $layanid          = $a->TEST_ID;
                        $sampelid         = $a->SAMPELID;
                        $transco          = $a->TRANS_CO;
                        $uuidspecimentlab = Satusehat::uuid();

                        if(SERVER === "production"){
                            $patientid        = $a->PATIENTID;
                            $mrpas            = $a->PATIENTMR;
                            $patientname      = $a->PATIENTNAME;
                        }else{
                            $resultgetRandomPatient      = Satusehat::getRandomPatient();
                            $resultgetRandomPractitioner = Satusehat::getRandomPractitioner();

                            $patientid        = $resultgetRandomPatient['ihs'];
                            $mrpas            = "123456";
                            $patientname      = $resultgetRandomPatient['nama'];
                        }

                        $specimentlabresourcecollectionmethodcoding['system']              = "http://snomed.info/sct";
                        $specimentlabresourcecollectionmethodcoding['code']                = "82078001";
                        $specimentlabresourcecollectionmethodcoding['display']             = "Collection of blood specimen for laboratory (procedure)";
                        $specimentlabresourcefastingStatusCodeableConceptcoding['system']  = "http://terminology.hl7.org/CodeSystem/v2-0916";
                        $specimentlabresourcefastingStatusCodeableConceptcoding['code']    = "NF";
                        $specimentlabresourcefastingStatusCodeableConceptcoding['display'] = "The patient indicated they did not fast prior to the procedure.";

                        $specimentlabresourceidentifier['system']                     = "http://sys-ids.kemkes.go.id/specimen/".RS_ID;
                        $specimentlabresourceidentifier['use']                        = "official";
                        $specimentlabresourceidentifier['value']                      = $sampelid;
                        $specimentlabresourceidentifier['assigner']['reference']      = "Organization/".RS_ID;
                        $specimentlabresourcetype['system']                           = "http://snomed.info/sct";
                        $specimentlabresourcetype['code']                             = "119297000";
                        $specimentlabresourcetype['display']                          = "Blood specimen (specimen)";
                        $specimentlabresourcecollectionmethod['coding'][]             = $specimentlabresourcecollectionmethodcoding;
                        $specimentlabresourcefastingStatusCodeableConcept['coding'][] = $specimentlabresourcefastingStatusCodeableConceptcoding;

                        $specimentlabresource['resourceType']                               = "Specimen";
                        $specimentlabresource['identifier'][]                               = $specimentlabresourceidentifier;
                        $specimentlabresource['status']                                     = "available";
                        $specimentlabresource['type']['coding'][]                           = $specimentlabresourcetype;
                        $specimentlabresource['collection']['method']                       = $specimentlabresourcecollectionmethod;
                        $specimentlabresource['collection']['collectedDateTime']            = $a->TGLORDER;
                        $specimentlabresource['collection']['fastingStatusCodeableConcept'] = $specimentlabresourcefastingStatusCodeableConcept;
                        $specimentlabresource['subject']['reference']                       = "Patient/".$patientid;
                        $specimentlabresource['subject']['display']                         = $patientname;
                        $specimentlabresource['receivedTime']                               = $a->TGLORDER;
                        $specimentlabresource['request'][]['reference']                     = "ServiceRequest/".$a->SERVICERQUEST_ID;

                        $specimentlab['fullUrl']           = "urn:uuid:".$uuidspecimentlab;
                        $specimentlab['request']['method'] = "POST";
                        $specimentlab['request']['url']    = "Specimen";
                        $specimentlab['resource']          = $specimentlabresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $specimentlab;

                        // $this->response($specimentlabresource);

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
                                $simpanlog['IDENTIFIER']    = $sampelid;
                                $simpanlog['SAMPEL_ID']     = $sampelid;
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
                                echo formatlogbundle($pasienid,$episodeid,'Specimen','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'Specimen','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'Specimen','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetServiceRequest = [];
                                            $responsegetServiceRequest = Satusehat::getdata("Specimen","identifier",$sampelid,self::$oauth['access_token']);

                                            if(isset($responsegetServiceRequest['entry'])){
                                                foreach($responsegetServiceRequest['entry'] as $responsegetServiceRequests){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['TRANS_CO']      = $transco;
                                                    $simpanlog['LAYAN_ID']      = $layanid;
                                                    $simpanlog['IDENTIFIER']    = $sampelid;
                                                    $simpanlog['SAMPEL_ID']     = $sampelid;
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
                                                    $statusMsg   = "GET SPECIMENT REQUEST BY SAMPEL ID";
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