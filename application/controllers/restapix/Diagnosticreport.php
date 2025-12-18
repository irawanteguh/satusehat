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

    class Diagnosticreport extends REST_Controller{
        public static $oauth;

        public function __construct(){
            parent::__construct();
            $this->load->model("Modeldiagnosticreport", "md");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
            $this->headerlog();
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

        public function diagnosticreportlab_post(){
            
            if(!isset(self::$oauth['issue'])){
                $result        = $this->md->diagnosticreportlab(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor      = "";
                        $statusMsg        = "";
                        
                        $body                                   = [];
                        $diagnosticreport                       = [];
                        $diagnosticreportresource               = [];
                        $diagnosticreportresourceidentifier     = [];
                        $diagnosticreportresourcecategory       = [];
                        $diagnosticreportresourcecategorycoding = [];
                        $diagnosticreportresourcecode           = [];
                        $diagnosticreportresourcesubject        = [];
                        $diagnosticreportresourceencounter      = [];
                        $diagnosticreportresourceperformer      = [];
                        $diagnosticreportresourceresult         = [];
                        $results = [];

                        $pasienid  = "";
                        $episodeid = "";
                        $poliid    = "";
                        $layanid   = "";
                        $transco   = "";
                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLIID;
                        $layanid   = $a->TEST_ID;
                        $transco   = $a->TRANS_CO;

                        $uuiddiagnosticreport = "";
                        $uuiddiagnosticreport = Satusehat::uuid();

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

                        $diagnosticreportresourcecategorycoding['system']  = "http://terminology.hl7.org/CodeSystem/v2-0074";
                        $diagnosticreportresourcecategorycoding['code']    = "CH";
                        $diagnosticreportresourcecategorycoding['display'] = "Chemistry";

                        $diagnosticreportresourceidentifier['system'] = "http://sys-ids.kemkes.go.id/diagnostic/".RS_ID."/lab";
                        $diagnosticreportresourceidentifier['use']    = "official";
                        $diagnosticreportresourceidentifier['value']  = $a->TRANS_CO;
                        $diagnosticreportresourcecategory['coding'][] = $diagnosticreportresourcecategorycoding;
                        $diagnosticreportresourcecode['system']         = "http://loinc.org";
                        $diagnosticreportresourcecode['code']           = "55231-5";
                        $diagnosticreportresourcecode['display']        = "Electrolytes panel - Blood";
                        $diagnosticreportresourcesubject['reference']   = "Patient/".$patientid;
                        // $diagnosticreportresourcesubject['display']     = $patientname;
                        $diagnosticreportresourceencounter['reference'] = "Encounter/".$a->RESOURCEID;
                        $diagnosticreportresourceperformer['reference'] = "Organization/".RS_ID;

                        $resultref = explode(',', $a->RESULTREF);
                        $seq = 1;
                        foreach ($resultref as $resultrefs) {
                            $refresults              = [];
                            $resultrefs              = trim($resultrefs);
                            $refresults['id']        = (string) $seq;
                            $refresults['reference'] = "Observation/".$resultrefs;

                            $diagnosticreportresourceresult[] = $refresults;
                            $seq++;
                        }

                        $diagnosticreportresource['resourceType']           = "DiagnosticReport";
                        $diagnosticreportresource['identifier'][]           = $diagnosticreportresourceidentifier;
                        $diagnosticreportresource['status']                 = "final";
                        $diagnosticreportresource['category'][]             = $diagnosticreportresourcecategory;
                        $diagnosticreportresource['code']['coding'][]       = $diagnosticreportresourcecode;
                        $diagnosticreportresource['subject']                = $diagnosticreportresourcesubject;
                        $diagnosticreportresource['encounter']              = $diagnosticreportresourceencounter;
                        $diagnosticreportresource['effectiveDateTime']      = $a->TGLSELESAI;
                        $diagnosticreportresource['issued']                 = $a->TGLSELESAI;
                        $diagnosticreportresource['performer'][]            = $diagnosticreportresourceperformer;
                        $diagnosticreportresource['result']                 = $diagnosticreportresourceresult;
                        $diagnosticreportresource['basedOn'][]['reference'] = "ServiceRequest/".$a->SERVICERQUESTID;

                        $diagnosticreport['fullUrl']           = "urn:uuid:".$uuiddiagnosticreport;
                        $diagnosticreport['request']['method'] = "POST";
                        $diagnosticreport['request']['url']    = "DiagnosticReport";
                        $diagnosticreport['resource']          = $diagnosticreportresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $diagnosticreport;

                        // $this->response($diagnosticreportresource);

                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);
                        
                        if(isset($response['entry'])){
                            foreach($response['entry'] as $entrys){
                                $simpanlog = [];

                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['LAYAN_ID']      = $layanid;
                                $simpanlog['TRANS_CO']      = $transco;
                                $simpanlog['SNOMED_ID']     = "";
                                $simpanlog['LOCATION']      = $entrys['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $entrys['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $entrys['response']['resourceID'];
                                $simpanlog['ETAG']          = $entrys['response']['etag'];
                                $simpanlog['STATUS']        = $entrys['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $entrys['response']['lastModified'];
                                $simpanlog['JENIS']         = "13";
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
    
                                $this->mss->insertdata($simpanlog);

                                $statusColor = "green";
                                $statusMsg   = "Success";
                                echo $this->formatlog($pasienid,$episodeid,'DiagnosticReport',$entrys['response']['resourceID'], $statusMsg,'white','light_yellow',$statusColor);
                            } 
                        }else{
                            if ($response === null) {
                                echo $this->formatlog($pasienid,$episodeid,'DiagnosticReport','','ERROR | response | NULL response from SATUSEHAT','white','light_yellow','red');
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
                                        echo $this->formatlog($pasienid,$episodeid,'DiagnosticReport','', $statusMsg,'white','light_yellow',$statusColor);
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