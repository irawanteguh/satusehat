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

    class Encounter extends REST_Controller{
        public static $oauth;

        public function __construct(){
            parent::__construct();
            $this->load->model("ModelEncounter", "md");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
            headerbundle();
        }
        

        public function poliklinik_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->poliklinik(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor      = "";
                        $statusMsg        = "";
                        $patientid        = "";
                        $mrpas            = "";
                        $patientname      = "";
                        $practitionerid   = "";
                        $practitionername = "";
                        $locationid       = "";
                        $locationname     = "";
                        $pasienid         = "";
                        $episodeid        = "";
                        $poliid           = "";
                        $dokterid         = "";
                        $uuidencounter    = "";


                        $body                     = [];
                        $encounter                = [];
                        $resource                 = [];
                        $diagnosis                = [];
                        $conditions               = [];
                        $resourceidentifier       = [];
                        $resourcelocation         = [];
                        $resourceparticipant      = [];
                        $resourceparticipanttype  = [];
                        $statusHistoryfinish      = [];
                        $statusHistoryinprogress  = [];
                        $statusHistorytriage      = [];
                        $statusHistoryperiodstart = [];
                        $statusHistoryplanned     = [];
                        $conditiondiag            = [];
                        $resultcekdataresouce     = [];

                        $uuidencounter = Satusehat::uuid();

                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLI_ID;
                        $dokterid  = $a->DOKTER_ID;

                        if(SERVER === "production"){
                            $patientid        = $a->PATIENTID;
                            $mrpas            = $a->PATIENTMR;
                            $patientname      = $a->PATIENTNAME;
                            $practitionerid   = $a->PRACTITIONERID;
                            $practitionername = $a->PRACTITIONERNAME;
                            $locationid       = $a->LOCATIONID;
                            $locationname     = $a->LOCATIONNAME;
                        }else{
                            $resultgetRandomPatient      = Satusehat::getRandomPatient();
                            $resultgetRandomPractitioner = Satusehat::getRandomPractitioner();
                            $patientid                   = $resultgetRandomPatient['ihs'];
                            $mrpas                       = "123456";
                            $patientname                 = $resultgetRandomPatient['nama'];
                            $practitionerid              = $resultgetRandomPractitioner['ihs'];
                            $practitionername            = $resultgetRandomPractitioner['nama'];
                            $locationid                  = "91b6b664-929e-4b67-802a-a0a86a607a0c";
                            $locationname                = $a->LOCATIONNAME;
                        }

                        ////////////////////////////////

                        $conditions = explode('|', $a->CONDITION);
                        $seq = 1;
                        foreach($conditions as $condition){
                            $uuidcondition                       = "";
                            $refcondition                        = [];
                            $refconditioncoding                  = [];
                            $conditionpost                       = [];
                            $conditionpostresource               = [];
                            $conditionpostresourcecategory       = [];
                            $conditionpostresourceclinicalStatus = [];
                            $conditionpostresourcecode           = [];
                            $conditionpostresourceidentifier     = [];

                            $condition     = trim($condition);
                            $parts         = explode(';', $condition, 2);
                            $code          = trim($parts[0]);
                            $display       = trim($parts[1]);
                            $uuidcondition = Satusehat::uuid();

                            $refconditioncoding['code']    = "DD";
                            $refconditioncoding['display'] = "Discharge diagnosis";
                            $refconditioncoding['system']  = "http://terminology.hl7.org/CodeSystem/diagnosis-role";

                            $refcondition['condition']['display']   = $display;
                            $refcondition['condition']['reference'] = "urn:uuid:".$uuidcondition;
                            $refcondition['rank']                   = (float) $seq;
                            $refcondition['use']['coding'][]        = $refconditioncoding;

                            $diagnosis[] = $refcondition;
                            $seq++;

                            $conditionpostresourcecategory['code']          = "encounter-diagnosis";
                            $conditionpostresourcecategory['display']       = "Encounter Diagnosis";
                            $conditionpostresourcecategory['system']        = "http://terminology.hl7.org/CodeSystem/condition-category";
                            $conditionpostresourceclinicalStatus['code']    = "active";
                            $conditionpostresourceclinicalStatus['display'] = "Active";
                            $conditionpostresourceclinicalStatus['system']  = "http://terminology.hl7.org/CodeSystem/condition-clinical";
                            $conditionpostresourcecode['code']              = $code;
                            $conditionpostresourcecode['display']           = $display;
                            $conditionpostresourcecode['system']            = "http://hl7.org/fhir/sid/icd-10";
                            $conditionpostresourceidentifier['system']      = "http://sys-ids.kemkes.go.id/condition/".RS_ID;
                            $conditionpostresourceidentifier['value']       = $episodeid;

                            $conditionpostresource['category'][]['coding'][]     = $conditionpostresourcecategory;
                            $conditionpostresource['clinicalStatus']['coding'][] = $conditionpostresourceclinicalStatus;
                            $conditionpostresource['code']['coding'][]           = $conditionpostresourcecode;
                            $conditionpostresource['encounter']['reference']     = "urn:uuid:".$uuidencounter;
                            $conditionpostresource['identifier'][]               = $conditionpostresourceidentifier;
                            $conditionpostresource['onsetDateTime']              = $a->INPROGRESSEND;
                            $conditionpostresource['recordedDate']               = $a->INPROGRESSEND;
                            $conditionpostresource['resourceType']               = "Condition";
                            $conditionpostresource['subject']['display']         = $patientname;
                            $conditionpostresource['subject']['reference']       = "Patient/".$patientid;

                            $conditionpost['fullUrl']           = "urn:uuid:".$uuidcondition;
                            $conditionpost['request']['method'] = "POST";
                            $conditionpost['request']['url']    = "Condition";
                            $conditionpost['resource']          = $conditionpostresource;

                            $conditiondiag[]   = $conditionpost;
                        }

                        $resourceparticipanttype['code']    = "ATND";
                        $resourceparticipanttype['display'] = "attender";
                        $resourceparticipanttype['system']  = "http://terminology.hl7.org/CodeSystem/v3-ParticipationType";

                        $resourceidentifier['system']                   = "http://sys-ids.kemkes.go.id/encounter/".RS_ID;
                        $resourceidentifier['value']                    = $episodeid;
                        $resourcelocation['location']['display']        = $locationname;
                        $resourcelocation['location']['reference']      = "Location/".$locationid;
                        $resourcelocation['period']['end']              = $a->FINISH;
                        $resourcelocation['period']['start']            = $a->PERIODSTART;
                        $resourceparticipant['individual']['display']   = $practitionername;
                        $resourceparticipant['individual']['reference'] = "Practitioner/".$practitionerid;
                        $resourceparticipant['type'][]['coding'][]      = $resourceparticipanttype;
                        
                        $resource['class']['code']                = "AMB";
                        $resource['class']['display']             = "ambulatory";
                        $resource['class']['system']              = "http://terminology.hl7.org/CodeSystem/v3-ActCode";
                        $resource['diagnosis']                    = $diagnosis;
                        $resource['identifier'][]                 = $resourceidentifier;
                        $resource['location'][]                   = $resourcelocation;
                        $resource['participant'][]                = $resourceparticipant;
                        $resource['period']['end']                = $a->FINISH;
                        $resource['period']['start']              = $a->PERIODSTART;
                        $resource['resourceType']                 = "Encounter";
                        $resource['serviceProvider']['reference'] = "Organization/".RS_ID;
                        $resource['status']                       = "finished";
                        if($a->PLANNED!=null){
                            $statusHistoryplanned['period']['start'] = $a->PLANNED;
                            $statusHistoryplanned['period']['end']   = $a->PLANNED;
                            $statusHistoryplanned['status']          = "planned";
                            $resource['statusHistory'][]             = $statusHistoryplanned;
                        }
                        if($a->PERIODSTART!=null){
                            $statusHistoryperiodstart['period']['start'] = $a->PERIODSTART;
                            $statusHistoryperiodstart['period']['end']   = $a->PERIODSTART;
                            $statusHistoryperiodstart['status']          = "arrived";
                            $resource['statusHistory'][]                 = $statusHistoryperiodstart;
                        }
                        if($a->TRIAGE!=null){
                            $statusHistorytriage['period']['start'] = $a->TRIAGE;
                            $statusHistorytriage['period']['end']   = $a->TRIAGE;
                            $statusHistorytriage['status']          = "triaged";
                            $resource['statusHistory'][]            = $statusHistorytriage;
                        }
                        if($a->INPROGRESSSTART!=null && $a->INPROGRESSEND!=null){
                            $statusHistoryinprogress['period']['start'] = $a->INPROGRESSSTART;
                            $statusHistoryinprogress['period']['end']   = $a->INPROGRESSEND;
                            $statusHistoryinprogress['status']          = "in-progress";
                            $resource['statusHistory'][]                = $statusHistoryinprogress;
                        }
                        if($a->FINISH!=null){
                            $statusHistoryfinish['period']['start'] = $a->FINISH;
                            $statusHistoryfinish['period']['end']   = $a->FINISH;
                            $statusHistoryfinish['status']          = "finished";
                            $resource['statusHistory'][]            = $statusHistoryfinish;
                        }
                        $resource['subject']['display']   = $patientname;
                        $resource['subject']['reference'] = "Patient/".$patientid;

                        $encounter['fullUrl']           = "urn:uuid:".$uuidencounter;
                        $encounter['request']['method'] = "POST";
                        $encounter['request']['url']    = "Encounter";
                        $encounter['resource']          = $resource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $encounter;
                        $body['entry']        = array_merge($body['entry'], $conditiondiag);

                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);

                        if(isset($response['entry'])){
                            foreach($response['entry'] as $entrys){
                                $simpanlog = [];

                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['DOKTER_ID']     = $dokterid;
                                $simpanlog['IDENTIFIER']    = $episodeid;
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
                                echo formatlogbundle($pasienid,$episodeid,'Encounter','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'Encounter','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'Encounter','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetencounter          = [];
                                            $responsegetcondition          = [];
                                            $responsegetconditionencounter = [];

                                            $responsegetencounter = Satusehat::getdata("Encounter","identifier",$episodeid,self::$oauth['access_token']);
                                            $responsegetcondition = Satusehat::getdata("Condition","identifier",$episodeid,self::$oauth['access_token']); 

                                            if(isset($responsegetencounter['entry'])){
                                                foreach($responsegetencounter['entry'] as $responsegetencounters){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['IDENTIFIER']    = $episodeid;
                                                    $simpanlog['LOCATION']      = $responsegetencounters['fullUrl']."/_history/".trim($responsegetencounters['resource']['meta']['versionId'], 'W/"');
                                                    $simpanlog['RESOURCE_TYPE'] = $responsegetencounters['resource']['resourceType'];
                                                    $simpanlog['RESOURCE_ID']   = $responsegetencounters['resource']['id'];
                                                    $simpanlog['ETAG']          = 'W/"' . $responsegetencounters['resource']['meta']['versionId'] . '"';
                                                    $simpanlog['STATUS']        = "201 Created";
                                                    $simpanlog['LAST_MODIFIED'] = $responsegetencounters['resource']['meta']['lastUpdated'];
                                                    $simpanlog['JENIS']         = "1";
                                                    $simpanlog['ENVIRONMENT']   = SERVER;
                                                    $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                    
                                                    $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetencounters['resource']['resourceType'],$responsegetencounters['resource']['id']);

                                                    if(empty($resultcekdataresouce)){
                                                        $this->mss->insertdata($simpanlog);
                                                    }

                                                    $statusColor = "yellow";
                                                    $statusMsg   = "GET ENCOUNTER BY EPISODE ID";
                                                    echo formatlogbundle($pasienid,$episodeid,$responsegetencounters['resource']['resourceType'],$responsegetencounters['resource']['id'], $statusMsg,$statusColor);
                                                }
                                            }

                                            if(isset($responsegetcondition['entry'])){
                                                foreach($responsegetcondition['entry'] as $responsegetconditions){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['IDENTIFIER']    = $episodeid;
                                                    $simpanlog['LOCATION']      = $responsegetconditions['fullUrl']."/_history/".trim($responsegetconditions['resource']['meta']['versionId'], 'W/"');
                                                    $simpanlog['RESOURCE_TYPE'] = $responsegetconditions['resource']['resourceType'];
                                                    $simpanlog['RESOURCE_ID']   = $responsegetconditions['resource']['id'];
                                                    $simpanlog['ETAG']          = 'W/"' . $responsegetconditions['resource']['meta']['versionId'] . '"';
                                                    $simpanlog['STATUS']        = "201 Created";
                                                    $simpanlog['LAST_MODIFIED'] = $responsegetconditions['resource']['meta']['lastUpdated'];
                                                    $simpanlog['JENIS']         = "1";
                                                    $simpanlog['ENVIRONMENT']   = SERVER;
                                                    $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                    
                                                    $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetconditions['resource']['resourceType'],$responsegetconditions['resource']['id']);

                                                    if(empty($resultcekdataresouce)){
                                                        $this->mss->insertdata($simpanlog);
                                                    }

                                                    $statusColor = "yellow";
                                                    $statusMsg   = "GET CONDITION BY EPISODE ID";
                                                    echo formatlogbundle($pasienid,$episodeid,$responsegetconditions['resource']['resourceType'],$responsegetconditions['resource']['id'], $statusMsg,$statusColor);
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