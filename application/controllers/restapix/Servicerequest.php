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

    class Servicerequest extends REST_Controller{
        public static $oauth;

        public function __construct(){
            parent::__construct();
            $this->load->model("Modelservicerequest", "md");
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

        public function servicerequestlab_post(){
            $this->headerlog();
            if(!isset(self::$oauth['issue'])){
                $result        = $this->md->orderlab(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor      = "";
                        $statusMsg        = "";
                        
                        $body                                = [];
                        $specimentlab                        = [];
                        $specimentlabresource                = [];
                        $specimentlabresourceidentifier      = [];
                        $specimentlabresourcecategory        = [];
                        $specimentlabresourcecategorycoding  = [];
                        $specimentlabresourcecode            = [];
                        $specimentlabresourcesubject         = [];
                        $specimentlabresourceencounter       = [];
                        $specimentlabresourcerequester       = [];
                        $specimentlabresourceperformer       = [];
                        $specimentlabresourcereasonReference = [];
                        $conditions                          = [];

                        $pasienid  = "";
                        $episodeid = "";
                        $poliid    = "";
                        $layanid   = "";
                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLIID;
                        $layanid   = $a->TEST_ID;

                        $uuidspecimentlab = "";
                        $uuidspecimentlab = Satusehat::uuid();

                        $patientid        = "";
                        $mrpas            = "";
                        $patientname      = "";
                        $practitionerid   = "";
                        $practitionername = "";

                        if(SERVER === "production"){
                            $patientid        = $a->PATIENTID;
                            $mrpas            = $a->PATIENTMR;
                            $patientname      = $a->PATIENTNAME;
                            $practitionerid   = $a->PRACTITIONERID;
                            $practitionername = $a->PRACTITIONERNAME;
                        }else{
                            $resultgetRandomPatient      = Satusehat::getRandomPatient();
                            $resultgetRandomPractitioner = Satusehat::getRandomPractitioner();

                            $patientid        = $resultgetRandomPatient['ihs'];
                            $mrpas            = "123456";
                            $patientname      = $resultgetRandomPatient['nama'];
                            $practitionerid   = $resultgetRandomPractitioner['ihs'];
                            $practitionername = $resultgetRandomPractitioner['nama'];
                        }

                        $specimentlabresourcecategorycoding['system']  = "http://snomed.info/sct";
                        $specimentlabresourcecategorycoding['code']    = "108252007";
                        $specimentlabresourcecategorycoding['display'] = "Laboratory procedure";

                        $specimentlabresourceidentifier['system']   = "http://sys-ids.kemkes.go.id/servicerequest/".RS_ID;
                        $specimentlabresourceidentifier['use']      = "official";
                        $specimentlabresourceidentifier['value']    = $a->TRANS_CO;
                        $specimentlabresourcecategory['coding'][]   = $specimentlabresourcecategorycoding;
                        $specimentlabresourcecode['system']         = "http://loinc.org";
                        $specimentlabresourcecode['code']           = "55231-5";
                        $specimentlabresourcecode['display']        = "Electrolytes panel - Blood";
                        $specimentlabresourcesubject['reference']   = "Patient/".$patientid;
                        $specimentlabresourcesubject['display']     = $patientname;
                        $specimentlabresourceencounter['reference'] = "Encounter/".$a->RESOURCEID;
                        $specimentlabresourceencounter['display']   = "Permintaan Pemeriksaan".$a->NAMAPELAYANAN." ".$a->TGLORDER;
                        $specimentlabresourcerequester['reference'] = "Practitioner/".$practitionerid;
                        $specimentlabresourcerequester['display']   = $practitionername;
                        $specimentlabresourceperformer['reference'] = "Practitioner/".$practitionerid;
                        $specimentlabresourceperformer['display']   = $practitionername;
                        $conditions = explode(',', $a->CONDITIONID);
                        foreach ($conditions as $conditionId) {
                            $refcondition = [];
                            $conditionId = trim($conditionId);
                            $refcondition['reference'] = "Condition/".$conditionId;

                            $specimentlabresourcereasonReference[] = $refcondition;
                        }

                        $specimentlabresource['resourceType']       = "ServiceRequest";
                        $specimentlabresource['identifier'][]       = $specimentlabresourceidentifier;
                        $specimentlabresource['status']             = "active";
                        $specimentlabresource['intent']             = "original-order";
                        $specimentlabresource['priority']           = "routine";
                        $specimentlabresource['category'][]         = $specimentlabresourcecategory;
                        $specimentlabresource['code']['coding'][]   = $specimentlabresourcecode;
                        $specimentlabresource['code']['text']       = $a->NAMAPELAYANAN;
                        $specimentlabresource['subject']            = $specimentlabresourcesubject;
                        $specimentlabresource['encounter']          = $specimentlabresourceencounter;
                        $specimentlabresource['occurrenceDateTime'] = $a->TGLORDER;
                        $specimentlabresource['authoredOn']         = $a->TGLORDER;
                        $specimentlabresource['requester']          = $specimentlabresourcerequester;
                        $specimentlabresource['note'][]['text']     = (isset($a->CATATAN) && trim($a->CATATAN) !== '') ? $a->CATATAN : '-';
                        $specimentlabresource['performer'][]        = $specimentlabresourceperformer;
                        $specimentlabresource['reasonReference']    = $specimentlabresourcereasonReference;

                        $specimentlab['fullUrl']           = "urn:uuid:".$uuidspecimentlab;
                        $specimentlab['request']['method'] = "POST";
                        $specimentlab['request']['url']    = "ServiceRequest";
                        $specimentlab['resource']          = $specimentlabresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $specimentlab;

                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);
                        
                        if(isset($response['entry'])){
                            foreach($response['entry'] as $entrys){
                                $simpanlog = [];

                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['LAYAN_ID']      = $layanid;
                                $simpanlog['SNOMED_ID']     = "";
                                $simpanlog['LOCATION']      = $entrys['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $entrys['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $entrys['response']['resourceID'];
                                $simpanlog['ETAG']          = $entrys['response']['etag'];
                                $simpanlog['STATUS']        = $entrys['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $entrys['response']['lastModified'];
                                $simpanlog['JENIS']          = "10";
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
    
                                $this->mss->insertdata($simpanlog);

                                $statusColor = "green";
                                $statusMsg   = "Success";
                                echo $this->formatlog($pasienid,$episodeid,'ServiceRequest',$entrys['response']['resourceID'], $statusMsg,'white','light_yellow',$statusColor);
                            } 
                        }else{
                            if ($response === null) {
                                echo $this->formatlog($pasienid,$episodeid,'ServiceRequest','','ERROR | response | NULL response from SATUSEHAT','white','light_yellow','red');
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
                                        echo $this->formatlog($pasienid,$episodeid,'ServiceRequest','', $statusMsg,'white','light_yellow',$statusColor);
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

        public function servicerequestrad_post(){
            $this->headerlog();
            if(!isset(self::$oauth['issue'])){
                $result        = $this->md->orderrad(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor      = "";
                        $statusMsg        = "";
                        
                        $body                                 = [];
                        $servicerequestrad                    = [];
                        $servicerequestradresource            = [];
                        
                        $servicerequestradresourcecategory = [];
                        $servicerequestradresourcecategorycoding = [];
                        $servicerequestradresourcecode = [];
                        $servicerequestradresourceencounter =[];
                        $servicerequestradresourceidentifier1 = [];
                        $servicerequestradresourceidentifier2 = [];
                        $servicerequestradresourceidentifier2type = [];
                        $servicerequestresourceorderDetail1 = [];
                        $servicerequestresourceorderDetail2 = [];
                        $servicerequestresourceorderDetail1coding = [];
                        $servicerequestresourceorderDetail2coding = [];
                        $servicerequestradresourcesubject = [];
                        $servicerequestradresourcerequester = [];
                        $servicerequestradresourceperformer = [];

                        $pasienid  = "";
                        $episodeid = "";
                        $poliid    = "";
                        $layanid   = "";
                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLIID;
                        $layanid   = $a->TEST_ID;

                        $uuidservicerequestrad = "";
                        $uuidservicerequestrad = Satusehat::uuid();

                        $patientid        = "";
                        $mrpas            = "";
                        $patientname      = "";
                        $practitionerid   = "";
                        $practitionername = "";

                        if(SERVER === "production"){
                            $patientid        = $a->PATIENTID;
                            $mrpas            = $a->PATIENTMR;
                            $patientname      = $a->PATIENTNAME;
                            $practitionerid   = $a->PRACTITIONERID;
                            $practitionername = $a->PRACTITIONERNAME;
                        }else{
                            $resultgetRandomPatient      = Satusehat::getRandomPatient();
                            $resultgetRandomPractitioner = Satusehat::getRandomPractitioner();

                            $patientid        = $resultgetRandomPatient['ihs'];
                            $mrpas            = "123456";
                            $patientname      = $resultgetRandomPatient['nama'];
                            $practitionerid   = $resultgetRandomPractitioner['ihs'];
                            $practitionername = $resultgetRandomPractitioner['nama'];
                        }

                        $servicerequestradresourcecategorycoding['system']  = "http://snomed.info/sct";
                        $servicerequestradresourcecategorycoding['code']    = "363679005";
                        $servicerequestradresourcecategorycoding['display'] = "Imaging";
                        $servicerequestradresourcecode['system']         = "http://loinc.org";
                        $servicerequestradresourcecode['code']           = "24648-8";
                        $servicerequestradresourcecode['display']        = "XR Chest PA upr";
                        $servicerequestradresourceidentifier2type['code']="ACSN";
                        $servicerequestradresourceidentifier2type['system']="http://terminology.hl7.org/CodeSystem/v2-0203";
                        $servicerequestresourceorderDetail1coding['code']      = "DX";
                        $servicerequestresourceorderDetail1coding['system']    = "http://dicom.nema.org/resources/ontology/DCM";
                        $servicerequestresourceorderDetail2coding['display']   = "XR0001";
                        $servicerequestresourceorderDetail2coding['system']    = "http://sys-ids.kemkes.go.id/ae-title";

                        
                        $servicerequestradresourcecategory['coding'][]=$servicerequestradresourcecategorycoding;
                        $servicerequestradresourceencounter['reference'] = "Encounter/".$a->RESOURCEID;
                        $servicerequestradresourceidentifier1['system'] = "http://sys-ids.kemkes.go.id/servicerequest/".RS_ID;
                        $servicerequestradresourceidentifier1['value']  = $a->TRANS_CO;
                        $servicerequestradresourceidentifier2['system'] = "http://sys-ids.kemkes.go.id/acsn/".RS_ID;
                        $servicerequestradresourceidentifier2['type']['coding'][]  = $servicerequestradresourceidentifier2type; 
                        $servicerequestradresourceidentifier2['use']  = "usual";
                        $servicerequestradresourceidentifier2['value']  = $a->TRANSRAD;
                        $servicerequestresourceorderDetail1['coding'][]        = $servicerequestresourceorderDetail1coding;
                        $servicerequestresourceorderDetail1['text']            = "Modality Code: DX";
                        $servicerequestresourceorderDetail2['coding'][]        = $servicerequestresourceorderDetail2coding;
                        $servicerequestradresourcesubject['reference']   = "Patient/".$patientid;
                        $servicerequestradresourcerequester['reference'] = "Practitioner/".$practitionerid;
                        $servicerequestradresourcerequester['display']   = $practitionername;
                        $servicerequestradresourceperformer['reference'] = "Practitioner/".$practitionerid;
                        $servicerequestradresourceperformer['display']   = $practitionername;

                        $servicerequestradresource['resourceType']       = "ServiceRequest";
                        $servicerequestradresource['status']             = "active";
                        $servicerequestradresource['intent']             = "original-order";
                        $servicerequestradresource['priority']           = "routine";
                        $servicerequestradresource['category'][]         = $servicerequestradresourcecategory;
                        $servicerequestradresource['code']['coding'][]   = $servicerequestradresourcecode;
                        $servicerequestradresource['code']['text']       = $a->NAMAPELAYANAN;
                        $servicerequestradresource['encounter']          = $servicerequestradresourceencounter;
                        $servicerequestradresource['identifier'][]       = $servicerequestradresourceidentifier1;
                        $servicerequestradresource['identifier'][]       = $servicerequestradresourceidentifier2;
                        $servicerequestradresource['occurrenceDateTime'] = $a->TGLORDER;
                        $servicerequestradresource['orderDetail'][]      = $servicerequestresourceorderDetail1;
                        $servicerequestradresource['orderDetail'][]      = $servicerequestresourceorderDetail2;
                        $servicerequestradresource['subject']            = $servicerequestradresourcesubject;
                        $servicerequestradresource['requester']          = $servicerequestradresourcerequester;
                        $servicerequestradresource['performer'][]          = $servicerequestradresourceperformer;

                        $servicerequestrad['fullUrl']           = "urn:uuid:".$uuidservicerequestrad;
                        $servicerequestrad['request']['method'] = "POST";
                        $servicerequestrad['request']['url']    = "ServiceRequest";
                        $servicerequestrad['resource']          = $servicerequestradresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $servicerequestrad;

                        // $this->response($servicerequestradresource);

                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);
                        
                        if(isset($response['entry'])){
                            foreach($response['entry'] as $entrys){
                                $simpanlog = [];

                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['LAYAN_ID']      = $layanid;
                                $simpanlog['SNOMED_ID']     = "";
                                $simpanlog['LOCATION']      = $entrys['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $entrys['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $entrys['response']['resourceID'];
                                $simpanlog['ETAG']          = $entrys['response']['etag'];
                                $simpanlog['STATUS']        = $entrys['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $entrys['response']['lastModified'];
                                $simpanlog['JENIS']          = "6";
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
    
                                $this->mss->insertdata($simpanlog);

                                $statusColor = "green";
                                $statusMsg   = "Success";
                                echo $this->formatlog($pasienid,$episodeid,'ServiceRequest',$entrys['response']['resourceID'], $statusMsg,'white','light_yellow',$statusColor);
                            } 
                        }else{
                            if ($response === null) {
                                echo $this->formatlog($pasienid,$episodeid,'ServiceRequest','','ERROR | response | NULL response from SATUSEHAT','white','light_yellow','red');
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
                                        echo $this->formatlog($pasienid,$episodeid,'ServiceRequest','', $statusMsg,'white','light_yellow',$statusColor);
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