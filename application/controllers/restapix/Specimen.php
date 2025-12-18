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
        }

        public function headerlog(){
            echo PHP_EOL;
            echo color('cyan').str_pad("PASIEN_ID", 10).str_pad("EPISODE_ID", 15).str_pad("RESOURCE_ID", 20).str_pad("SATUSEHAT_ID", 38)."MESSAGE".PHP_EOL;
        }

        public function formatlog(
            $pasienId,
            $episodeId,
            $resourceId,
            $satusehatId,
            $message,
            $colorIdentity = 'cyan',
            $colorUser = 'yellow',
            $colorMessage = 'white'
        ){
            $widthPasien     = 10;
            $widthEpisode    = 15;
            $widthResource   = 20;
            $widthSatuSehat  = 38;

            $reset = color('reset');

            $formatted  = color($colorIdentity) . str_pad($pasienId, $widthPasien) . $reset;
            $formatted .= color($colorIdentity) . str_pad($episodeId, $widthEpisode) . $reset;
            $formatted .= color($colorIdentity) . str_pad($resourceId, $widthResource) . $reset;
            $formatted .= color($colorIdentity) . str_pad($satusehatId, $widthSatuSehat) . $reset;
            $formatted .= color($colorMessage) . $message . $reset;

            return $formatted . PHP_EOL;
        }

        public function specimenlab_post(){
            $this->headerlog();
            if(!isset(self::$oauth['issue'])){
                $result        = $this->md->specimentlab(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor      = "";
                        $statusMsg        = "";
                        
                        $body                                                   = [];
                        $specimentlab                                           = [];
                        $specimentlabresource                                   = [];
                        $specimentlabresourceidentifier                         = [];
                        $specimentlabresourcetype                               = [];
                        $specimentlabresourcecollectionmethod                   = [];
                        $specimentlabresourcecollectionmethodcoding             = [];
                        $specimentlabresourcefastingStatusCodeableConcept       = [];
                        $specimentlabresourcefastingStatusCodeableConceptcoding = [];
                        $specimentlabresourcesubject                            = [];
                        $specimentlabresourcerequester                          = [];

                        $pasienid  = "";
                        $episodeid = "";
                        $poliid    = "";
                        $layanid   = "";
                        $sampelid  = "";
                        $transco   = "";
                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLIID;
                        $layanid   = $a->TEST_ID;
                        $sampelid  = $a->SAMPELID;
                        $transco   = $a->TRANS_CO;

                        $uuidspecimentlab = "";
                        $uuidspecimentlab = Satusehat::uuid();

                        $patientid        = "";
                        $mrpas            = "";
                        $patientname      = "";

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
                        $specimentlabresourcesubject['reference']                     = "Patient/".$patientid;
                        $specimentlabresourcesubject['display']                       = $patientname;
                        $specimentlabresourcerequester['reference']                   = "ServiceRequest/".$a->SERVICERQUESTID;

                        $specimentlabresource['resourceType']                               = "Specimen";
                        $specimentlabresource['identifier'][]                               = $specimentlabresourceidentifier;
                        $specimentlabresource['status']                                     = "available";
                        $specimentlabresource['type']['coding'][]                           = $specimentlabresourcetype;
                        $specimentlabresource['collection']['method']                       = $specimentlabresourcecollectionmethod;
                        $specimentlabresource['collection']['collectedDateTime']            = $a->TGLORDER;
                        $specimentlabresource['collection']['fastingStatusCodeableConcept'] = $specimentlabresourcefastingStatusCodeableConcept;
                        $specimentlabresource['subject']                                    = $specimentlabresourcesubject;
                        $specimentlabresource['receivedTime']                               = $a->TGLORDER;
                        $specimentlabresource['request'][]                                  = $specimentlabresourcerequester;

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
                                $simpanlog['LAYAN_ID']      = $layanid;
                                $simpanlog['SAMPEL_ID']     = $sampelid;
                                $simpanlog['TRANS_CO']      = $transco;
                                $simpanlog['SNOMED_ID']     = "";
                                $simpanlog['LOCATION']      = $entrys['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $entrys['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $entrys['response']['resourceID'];
                                $simpanlog['ETAG']          = $entrys['response']['etag'];
                                $simpanlog['STATUS']        = $entrys['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $entrys['response']['lastModified'];
                                $simpanlog['JENIS']         = "11";
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
    
                                $this->mss->insertdata($simpanlog);

                                $statusColor = "green";
                                $statusMsg   = "Success";
                                echo $this->formatlog($pasienid,$episodeid,'Specimen',$entrys['response']['resourceID'], $statusMsg,'white','light_yellow',$statusColor);
                            } 
                        }else{
                            if ($response === null) {
                                echo $this->formatlog($pasienid,$episodeid,'Specimen','','ERROR | response | NULL response from SATUSEHAT','white','light_yellow','red');
                            }else{
                                if(isset($response['issue']) && is_array($response['issue']) && count($response['issue']) > 0){
                                    foreach ($response['issue'] as $issues) {
                                        $severity = isset($issues['severity']) ? $issues['severity'] : 'unknown';
                                        $code     = isset($issues['code']) ? $issues['code'] : '-';
                                        $text     = isset($issues['details']['text']) ? $issues['details']['text'] : '-';
                                        
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

                                        $statusMsg = strtoupper($severity) . ' | ' . $code . ' | ' . $text;
                                        echo $this->formatlog($pasienid,$episodeid,'Specimen','', $statusMsg,'white','light_yellow',$statusColor);
                                    }
                                }
                            }
                        }
                    }
                }else{
                    echo color('red')."Data Tidak Ditemukan";
                }
            }else{
                echo color('red').self::$oauth['issue'];
            }
        }
    }

?>