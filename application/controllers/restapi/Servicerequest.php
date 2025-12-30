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
            headerbundle();
        }
        
        public function orderrad_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->orderrad(SERVER);
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
                        $acsn                = "";
                        $layanid             = "";
                        $dokterid            = "";
                        $identifier          = "";
                        $uuidservicerequest  = "";

                        $body                                     = [];
                        $servicerequest                           = [];
                        $servicerequestresource                   = [];
                        $servicerequestresourcecategory           = [];
                        $servicerequestresourcecategorycoding     = [];
                        $servicerequestresourcecode               = [];
                        $servicerequestresourceidentifier1        = [];
                        $servicerequestresourceidentifier2        = [];
                        $servicerequestresourceidentifier2type    = [];
                        $servicerequestresourceorderDetail1       = [];
                        $servicerequestresourceorderDetail2       = [];
                        $servicerequestresourceorderDetail1coding = [];
                        $servicerequestresourceorderDetail2coding = [];
                        $servicerequestresourceperformer          = [];
                        $servicerequestresourcerequester          = [];

                        $uuidservicerequest = Satusehat::uuid();

                        $pasienid   = $a->PASIEN_ID;
                        $episodeid  = $a->EPISODE_ID;
                        $poliid     = $a->POLI_ID;
                        $transco    = $a->TRANS_CO;
                        $acsn       = $a->TRANS_RAD;
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

                        $servicerequestresourcecategorycoding['system']      = "http://snomed.info/sct";
                        $servicerequestresourcecategorycoding['code']        = "363679005";
                        $servicerequestresourcecategorycoding['display']     = "Imaging";
                        $servicerequestresourceidentifier2type['code']       = "ACSN";
                        $servicerequestresourceidentifier2type['system']     = "http://terminology.hl7.org/CodeSystem/v2-0203";
                        $servicerequestresourceorderDetail1coding['code']    = "DX";
                        $servicerequestresourceorderDetail1coding['system']  = "http://dicom.nema.org/resources/ontology/DCM";
                        $servicerequestresourceorderDetail2coding['display'] = "XR0001";
                        $servicerequestresourceorderDetail2coding['system']  = "http://sys-ids.kemkes.go.id/ae-title";

                        $servicerequestresourcecategory['coding'][]            = $servicerequestresourcecategorycoding;
                        $servicerequestresourcecode['system']                  = "http://loinc.org";
                        $servicerequestresourcecode['code']                    = $a->LOINCCODE;
                        $servicerequestresourcecode['display']                 = $a->LOINCDESC;
                        $servicerequestresourceidentifier1['system']           = "http://sys-ids.kemkes.go.id/servicerequest/".RS_ID;
                        $servicerequestresourceidentifier1['value']            = $identifier;
                        $servicerequestresourceidentifier2['system']           = "http://sys-ids.kemkes.go.id/acsn/".RS_ID;
                        $servicerequestresourceidentifier2['type']['coding'][] = $servicerequestresourceidentifier2type;
                        $servicerequestresourceidentifier2['use']              = "usual";
                        $servicerequestresourceidentifier2['value']            = $acsn;
                        $servicerequestresourceorderDetail1['coding'][]        = $servicerequestresourceorderDetail1coding;
                        $servicerequestresourceorderDetail1['text']            = "Modality Code: DX";
                        $servicerequestresourceorderDetail2['coding'][]        = $servicerequestresourceorderDetail2coding;
                        $servicerequestresourceperformer['reference']          = "Practitioner/".$practitionerradid;
                        $servicerequestresourceperformer['display']            = $practitionerradname;
                        $servicerequestresourcerequester['reference']          = "Practitioner/".$practitionerid;
                        $servicerequestresourcerequester['display']            = $practitionername;
                        

                        $servicerequestresource['category'][]             = $servicerequestresourcecategory;
                        $servicerequestresource['code']['coding'][]       = $servicerequestresourcecode;
                        $servicerequestresource['code']['text']           = "Pemeriksaan ".$a->NAMAPELAYANAN;
                        $servicerequestresource['encounter']['reference'] = "Encounter/".$a->RESOURCE_ID;
                        $servicerequestresource['identifier'][]           = $servicerequestresourceidentifier1;
                        $servicerequestresource['identifier'][]           = $servicerequestresourceidentifier2;
                        $servicerequestresource['intent']                 = "original-order";
                        $servicerequestresource['occurrenceDateTime']     = $a->TGLORDER;
                        $servicerequestresource['orderDetail'][]          = $servicerequestresourceorderDetail1;
                        $servicerequestresource['orderDetail'][]          = $servicerequestresourceorderDetail2;
                        $servicerequestresource['performer'][]            = $servicerequestresourceperformer;
                        $servicerequestresource['priority']               = "routine";
                        $servicerequestresource['requester']              = $servicerequestresourcerequester;
                        $servicerequestresource['resourceType']           = "ServiceRequest";
                        $servicerequestresource['status']                 = "active";
                        $servicerequestresource['subject']['reference']   = "Patient/".$patientid;

                        $servicerequest['fullUrl']           = "urn:uuid:".$uuidservicerequest;
                        $servicerequest['request']['method'] = "POST";
                        $servicerequest['request']['url']    = "ServiceRequest";
                        $servicerequest['resource']          = $servicerequestresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $servicerequest;

                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);

                        if(isset($response['entry'])){
                            foreach($response['entry'] as $entrys){
                                $simpanlog = [];

                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['DOKTER_ID']     = $dokterid;
                                $simpanlog['TRANS_CO']      = $transco;
                                $simpanlog['ACSN']          = $acsn;
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
                                echo formatlogbundle($pasienid,$episodeid,'ServiceRequest','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'ServiceRequest','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'ServiceRequest','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetServiceRequest = [];
                                            $parameter                 = "http://sys-ids.kemkes.go.id/acsn/".RS_ID."|".$acsn;
                                            $responsegetServiceRequest = Satusehat::getdata("ServiceRequest","identifier",$parameter,self::$oauth['access_token']);

                                            if(isset($responsegetServiceRequest['entry'])){
                                                foreach($responsegetServiceRequest['entry'] as $responsegetServiceRequests){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['TRANS_CO']      = $transco;
                                                    $simpanlog['LAYAN_ID']      = $layanid;
                                                    $simpanlog['ACSN']          = $acsn;
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
                                                    $statusMsg   = "GET SERVICE REQUEST BY ACSN";
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

        public function orderlab_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->orderlab(SERVER);
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
                        $uuidservicerequest  = "";

                        $body                                  = [];
                        $servicerequest                        = [];
                        $servicerequestresource                = [];
                        $servicerequestresourceidentifier      = [];
                        $servicerequestresourcecategory        = [];
                        $servicerequestresourcecategorycoding  = [];
                        $servicerequestresourcecode            = [];
                        $servicerequestresourceperformer       = [];
                        $servicerequestresourceperformer       = [];
                        $servicerequestresourcereasonReference = [];
                        $conditions                            = [];

                        $uuidservicerequest = Satusehat::uuid();

                        $pasienid   = $a->PASIEN_ID;
                        $episodeid  = $a->EPISODE_ID;
                        $poliid     = $a->POLI_ID;
                        $transco    = $a->TRANS_CO;
                        $layanid    = $a->TEST_ID;
                        $dokterid   = $a->DOKTER_ID;
                        $identifier = $a->TRANS_CO;

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

                        $servicerequestresourcecategorycoding['system']  = "http://snomed.info/sct";
                        $servicerequestresourcecategorycoding['code']    = "108252007";
                        $servicerequestresourcecategorycoding['display'] = "Laboratory procedure";

                        $servicerequestresourceidentifier['system']   = "http://sys-ids.kemkes.go.id/servicerequest/".RS_ID;
                        $servicerequestresourceidentifier['use']      = "official";
                        $servicerequestresourceidentifier['value']    = $identifier;
                        $servicerequestresourcecategory['coding'][]   = $servicerequestresourcecategorycoding;
                        $servicerequestresourcecode['system']         = "http://loinc.org";
                        $servicerequestresourcecode['code']           = "55231-5";
                        $servicerequestresourcecode['display']        = "Electrolytes panel - Blood";
                        $servicerequestresourceperformer['reference'] = "Practitioner/".$practitionerid;
                        $servicerequestresourceperformer['display']   = $practitionername;
                        $conditions = explode(',', $a->CONDITIONID);
                        foreach ($conditions as $conditionId) {
                            $refcondition = [];
                            $conditionId = trim($conditionId);
                            $refcondition['reference'] = "Condition/".$conditionId;

                            $specimentlabresourcereasonReference[] = $refcondition;
                        }
                        
                        $servicerequestresource['resourceType']           = "ServiceRequest";
                        $servicerequestresource['identifier'][]           = $servicerequestresourceidentifier;
                        $servicerequestresource['status']                 = "active";
                        $servicerequestresource['intent']                 = "original-order";
                        $servicerequestresource['priority']               = "routine";
                        $servicerequestresource['category'][]             = $servicerequestresourcecategory;
                        $servicerequestresource['code']['coding'][]       = $servicerequestresourcecode;
                        $servicerequestresource['code']['text']           = $a->NAMAPELAYANAN;
                        $servicerequestresource['subject']['reference']   = "Patient/".$patientid;
                        $servicerequestresource['subject']['display']     = $patientname;
                        $servicerequestresource['encounter']['reference'] = "Encounter/".$a->RESOURCE_ID;
                        $servicerequestresource['encounter']['display']   = "Permintaan Pemeriksaan".$a->NAMAPELAYANAN." ".$a->TGLORDER;
                        $servicerequestresource['occurrenceDateTime']     = $a->TGLORDER;
                        $servicerequestresource['authoredOn']             = $a->TGLORDER;
                        $servicerequestresource['requester']['reference'] = "Practitioner/".$practitionerid;
                        $servicerequestresource['requester']['display']   = $practitionername;
                        $servicerequestresource['note'][]['text']         = (isset($a->CATATAN) && trim($a->CATATAN) !== '') ? $a->CATATAN : '-';
                        $servicerequestresource['performer'][]            = $servicerequestresourceperformer;
                        $servicerequestresource['reasonReference']        = $servicerequestresourcereasonReference;

                        $servicerequest['fullUrl']           = "urn:uuid:".$uuidservicerequest;
                        $servicerequest['request']['method'] = "POST";
                        $servicerequest['request']['url']    = "ServiceRequest";
                        $servicerequest['resource']          = $servicerequestresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $servicerequest;

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
                                $simpanlog['JENIS']         = "2";
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
                                echo formatlogbundle($pasienid,$episodeid,'ServiceRequest','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'ServiceRequest','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'ServiceRequest','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetServiceRequest = [];
                                            $responsegetServiceRequest = Satusehat::getdata("ServiceRequest","identifier",$identifier,self::$oauth['access_token']);

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
                                                    $simpanlog['JENIS']         = "2";
                                                    
                                                    $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetServiceRequests['resource']['resourceType'],$responsegetServiceRequests['resource']['id']);
                                                    if(empty($resultcekdataresouce)){
                                                        $this->mss->insertdata($simpanlog);
                                                    }

                                                    $statusColor = "yellow";
                                                    $statusMsg   = "GET SERVICE REQUEST BY TRANS CO";
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