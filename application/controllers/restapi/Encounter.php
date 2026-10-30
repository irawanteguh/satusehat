<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    date_default_timezone_set('Asia/Jakarta');
    require_once APPPATH . 'core/MY_Satusehat_Controller.php';

    if(DEBUG === false){
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
    }
    
    class Encounter extends MY_Satusehat_Controller{

        public function __construct(){
            parent::__construct();
            $this->load->model("ModelEncounter", "md");
        }
        
        public function poliklinik_post(){
            if(!isset(self::$oauth['issue'])){

                $result = $this->md->poliklinik(SERVER);

                if(!empty($result)){
                    foreach ($result as $a){
                        $uuidencounter    = "";
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

                        $response                                           = [];
                        $body                                               = [];
                        $encounter                                          = [];
                        $encounterresource                                  = [];
                        $encounterresourceclass                             = [];
                        $encounterresourceidentifier                        = [];
                        $encounterresourcelocation                          = [];
                        $encounterresourcelocationextension                 = [];
                        $encounterresourcelocationextensionextension        = [];
                        $encounterresourcelocationextensionextension1       = [];
                        $encounterresourcelocationextensionextension2       = [];
                        $encounterresourcelocationextensionextension1coding = [];
                        $encounterresourcelocationextensionextension2coding = [];
                        $encounterresourcelocationlocation                  = [];
                        $encounterresourcelocationperiod                    = [];
                        $encounterresourceparticipant                       = [];
                        $encounterresourceparticipanttypecoding             = [];
                        $statusHistoryperiodstart                           = [];
                        $statusHistoryinprogress                            = [];
                        $statusHistoryfinish                                = [];

                        $conditions    = [];
                        $diagnosis     = [];
                        $conditiondiag = [];

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

                        $conditions = explode('|', $a->CONDITION);
                        $seq        = 1;
                        foreach($conditions as $condition){

                            $refcondition       = [];
                            $refconditioncoding = [];

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

                            $uuidcondition = "";
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
                            $conditionpostresourceidentifier['use']         = "official";
                            $conditionpostresourceidentifier['value']       = $episodeid;

                            $conditionpostresource['category'][]['coding'][]     = $conditionpostresourcecategory;
                            $conditionpostresource['clinicalStatus']['coding'][] = $conditionpostresourceclinicalStatus;
                            $conditionpostresource['code']['coding'][]           = $conditionpostresourcecode;
                            $conditionpostresource['encounter']['reference']     = "urn:uuid:".$uuidencounter;
                            $conditionpostresource['identifier'][]               = $conditionpostresourceidentifier;
                            $conditionpostresource['onsetDateTime']              = $a->INPROGRESSEND;
                            $conditionpostresource['recordedDate']               = $a->INPROGRESSEND;
                            $conditionpostresource['recorder']['display']        = $practitionername;
                            $conditionpostresource['recorder']['reference']      = "Practitioner/".$practitionerid;
                            $conditionpostresource['resourceType']               = "Condition";
                            $conditionpostresource['subject']['display']         = $patientname;
                            $conditionpostresource['subject']['reference']       = "Patient/".$patientid;

                            $conditionpost['fullUrl']           = "urn:uuid:".$uuidcondition;
                            $conditionpost['request']['method'] = "POST";
                            $conditionpost['request']['url']    = "Condition";
                            $conditionpost['resource']          = $conditionpostresource;

                            $conditiondiag[]   = $conditionpost;
                        }

                        $encounterresourcelocationextensionextension1coding['code']    = "reguler";
                        $encounterresourcelocationextensionextension1coding['display'] = "Kelas Reguler";
                        $encounterresourcelocationextensionextension1coding['system']  = "http://terminology.kemkes.go.id/CodeSystem/locationServiceClass-Outpatient";
                        $encounterresourcelocationextensionextension2coding['code']    = "kelas-tetap";
                        $encounterresourcelocationextensionextension2coding['display'] = "Kelas Tetap Perawatan";
                        $encounterresourcelocationextensionextension2coding['system']  = "http://terminology.kemkes.go.id/CodeSystem/locationUpgradeClass";

                        $encounterresourcelocationextensionextension1['url']                              = "value";
                        $encounterresourcelocationextensionextension1['valueCodeableConcept']['coding'][] = $encounterresourcelocationextensionextension1coding;
                        $encounterresourcelocationextensionextension2['url']                              = "upgradeClassIndicator";
                        $encounterresourcelocationextensionextension2['valueCodeableConcept']['coding'][] = $encounterresourcelocationextensionextension2coding;
                        
                        $encounterresourcelocationextensionextension[]=$encounterresourcelocationextensionextension1;
                        $encounterresourcelocationextensionextension[]=$encounterresourcelocationextensionextension2;

                        $encounterresourcelocationextension['extension']   = $encounterresourcelocationextensionextension;
                        $encounterresourcelocationextension['url']         = "https://fhir.kemkes.go.id/r4/StructureDefinition/ServiceClass";
                        $encounterresourcelocationlocation['display']      = $locationname;
                        $encounterresourcelocationlocation['reference']    = "Location/".$locationid;
                        $encounterresourcelocationperiod['end']            = $a->FINISH;
                        $encounterresourcelocationperiod['start']          = $a->PERIODSTART;
                        $encounterresourceparticipanttypecoding['code']    = "ATND";
                        $encounterresourceparticipanttypecoding['display'] = "attender";
                        $encounterresourceparticipanttypecoding['system']  = "http://terminology.hl7.org/CodeSystem/v3-ParticipationType";

                        $encounterresourceclass['code']                          = "AMB";
                        $encounterresourceclass['display']                       = "ambulatory";
                        $encounterresourceclass['system']                        = "http://terminology.hl7.org/CodeSystem/v3-ActCode";
                        $encounterresourceidentifier['system']                   = "http://sys-ids.kemkes.go.id/encounter/".RS_ID;
                        $encounterresourceidentifier['use']                      = "official";
                        $encounterresourceidentifier['value']                    = $episodeid;
                        $encounterresourcelocation['extension'][]                = $encounterresourcelocationextension;
                        $encounterresourcelocation['location']                   = $encounterresourcelocationlocation;
                        $encounterresourcelocation['period']                     = $encounterresourcelocationperiod;
                        $encounterresourceparticipant['individual']['display']   = $practitionername;
                        $encounterresourceparticipant['individual']['reference'] = "Practitioner/".$practitionerid;
                        $encounterresourceparticipant['type'][]['coding'][]      = $encounterresourceparticipanttypecoding;

                        $encounterresource['class']                        = $encounterresourceclass;
                        $encounterresource['diagnosis']                    = $diagnosis;
                        $encounterresource['identifier'][]                 = $encounterresourceidentifier;
                        $encounterresource['location'][]                   = $encounterresourcelocation;
                        $encounterresource['participant'][]                = $encounterresourceparticipant;
                        $encounterresource['period']['end']                = $a->FINISH;
                        $encounterresource['period']['start']              = $a->PERIODSTART;
                        $encounterresource['resourceType']                 = "Encounter";
                        $encounterresource['serviceProvider']['reference'] = "Organization/".RS_ID;
                        $encounterresource['status']                       = "finished";
                        if($a->PLANNED!=null){
                            $statusHistoryplanned['period']['start'] = $a->PLANNED;
                            $statusHistoryplanned['period']['end']   = $a->PLANNED;
                            $statusHistoryplanned['status']          = "planned";
                            $encounterresource['statusHistory'][]    = $statusHistoryplanned;
                        }
                        if($a->PERIODSTART!=null){
                            $statusHistoryperiodstart['period']['start'] = $a->PERIODSTART;
                            $statusHistoryperiodstart['period']['end']   = $a->PERIODSTART;
                            $statusHistoryperiodstart['status']          = "arrived";
                            $encounterresource['statusHistory'][]        = $statusHistoryperiodstart;
                        }
                        if($a->TRIAGE!=null){
                            $statusHistorytriage['period']['start'] = $a->TRIAGE;
                            $statusHistorytriage['period']['end']   = $a->TRIAGE;
                            $statusHistorytriage['status']          = "triaged";
                            $encounterresource['statusHistory'][]   = $statusHistorytriage;
                        }
                        if($a->INPROGRESSSTART!=null && $a->INPROGRESSEND!=null && $a->INPROGRESSSTART != 'T+00:00' && $a->INPROGRESSEND != 'T+00:00'){
                            $statusHistoryinprogress['period']['start'] = $a->INPROGRESSSTART;
                            if (strtotime($a->INPROGRESSEND) < strtotime($a->INPROGRESSSTART)) {
                                $statusHistoryinprogress['period']['end'] = $a->INPROGRESSSTART;
                            } else {
                                $statusHistoryinprogress['period']['end'] = $a->INPROGRESSEND;
                            }
                            $statusHistoryinprogress['status']    = "in-progress";
                            $encounterresource['statusHistory'][] = $statusHistoryinprogress;
                        }
                        if($a->FINISH!=null){
                            $statusHistoryfinish['period']['start'] = $a->FINISH;
                            $statusHistoryfinish['period']['end']   = $a->FINISH;
                            $statusHistoryfinish['status']          = "finished";
                            $encounterresource['statusHistory'][]   = $statusHistoryfinish;
                        }
                        $encounterresource['subject']['display']   = $patientname;
                        $encounterresource['subject']['reference'] = "Patient/".$patientid;

                        $encounter['fullUrl']           = "urn:uuid:".$uuidencounter;
                        $encounter['request']['method'] = "POST";
                        $encounter['request']['url']    = "Encounter";
                        $encounter['resource']          = $encounterresource;
                        
                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $encounter;
                        $body['entry']        = array_merge($body['entry'], $conditiondiag);

                        $response = Satusehat::postbundle(json_encode($body),$this->oauth['access_token']);

                        if($response === null){
                            echo formatlogbundle($pasienid,$episodeid,"Encounter","********", "ERROR | response | NULL response from SATUSEHAT","red");
                        }else{
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
                                if(isset($response['issue']) && is_array($response['issue']) && count($response['issue']) > 0){
                                    if($response['issue'][0]['code']==="duplicate"){
                                        $responsegetencounter = [];
                                        $responsegetencounter = Satusehat::getdata("Encounter","identifier",$episodeid,$this->oauth['access_token']);

                                        if(isset($responsegetencounter['entry'])){
                                            foreach($responsegetencounter['entry'] as $responsegetencounters){
                                                $simpanlog = [];

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
                                                $statusMsg   = "Duplicate | Encounter | Success Save Log Transaction";
                                                echo formatlogbundle($pasienid,$episodeid,$responsegetencounters['resource']['resourceType'],$responsegetencounters['resource']['id'], $statusMsg,$statusColor);
                                            }
                                        }

                                        $responsegetcondition = [];
                                        $responsegetcondition = Satusehat::getdata("Condition","identifier",$episodeid,$this->oauth['access_token']);

                                        if(isset($responsegetcondition['entry'])){
                                            foreach($responsegetcondition['entry'] as $responsegetconditions){
                                                $simpanlog = [];

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
                                                $statusMsg   = "Duplicate | Condition | Success Save Log Transaction";
                                                echo formatlogbundle($pasienid,$episodeid,$responsegetconditions['resource']['resourceType'],$responsegetconditions['resource']['id'], $statusMsg,$statusColor);
                                            }
                                        }

                                    }
                                }

                                if($response['issue'][0]['code']==="value"){
                                    $statusColor = "red";
                                    $statusMsg   = $response['issue'][0]['details']['text'];
                                    echo formatlogbundle($pasienid,$episodeid,$response['issue'][0]['expression'][0],"********", $statusMsg,$statusColor);
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

        public function igd_post(){
            if(!isset(self::$oauth['issue'])){

                $result = $this->md->igd(SERVER);

                if(!empty($result)){
                    foreach ($result as $a){
                        $uuidencounter    = "";
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

                        $response                                           = [];
                        $body                                               = [];
                        $encounter                                          = [];
                        $encounterresource                                  = [];
                        $encounterresourceclass                             = [];
                        $encounterresourceidentifier                        = [];
                        $encounterresourcelocation                          = [];
                        $encounterresourcelocationextension                 = [];
                        $encounterresourcelocationextensionextension        = [];
                        $encounterresourcelocationextensionextension1       = [];
                        $encounterresourcelocationextensionextension2       = [];
                        $encounterresourcelocationextensionextension1coding = [];
                        $encounterresourcelocationextensionextension2coding = [];
                        $encounterresourcelocationlocation                  = [];
                        $encounterresourcelocationperiod                    = [];
                        $encounterresourceparticipant                       = [];
                        $encounterresourceparticipanttypecoding             = [];
                        $statusHistoryperiodstart                           = [];
                        $statusHistoryinprogress                            = [];
                        $statusHistoryfinish                                = [];

                        $conditions    = [];
                        $diagnosis     = [];
                        $conditiondiag = [];

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

                        $conditions = explode('|', $a->CONDITION);
                        $seq        = 1;
                        foreach($conditions as $condition){

                            $refcondition       = [];
                            $refconditioncoding = [];

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

                            $uuidcondition = "";
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
                            $conditionpostresource['recorder']['display']        = $practitionername;
                            $conditionpostresource['recorder']['reference']      = "Practitioner/".$practitionerid;
                            $conditionpostresource['resourceType']               = "Condition";
                            $conditionpostresource['subject']['display']         = $patientname;
                            $conditionpostresource['subject']['reference']       = "Patient/".$patientid;

                            $conditionpost['fullUrl']           = "urn:uuid:".$uuidcondition;
                            $conditionpost['request']['method'] = "POST";
                            $conditionpost['request']['url']    = "Condition";
                            $conditionpost['resource']          = $conditionpostresource;

                            $conditiondiag[]   = $conditionpost;
                        }

                        $encounterresourcelocationextensionextension1coding['code']    = "reguler";
                        $encounterresourcelocationextensionextension1coding['display'] = "Kelas Reguler";
                        $encounterresourcelocationextensionextension1coding['system']  = "http://terminology.kemkes.go.id/CodeSystem/locationServiceClass-Outpatient";
                        $encounterresourcelocationextensionextension2coding['code']    = "kelas-tetap";
                        $encounterresourcelocationextensionextension2coding['display'] = "Kelas Tetap Perawatan";
                        $encounterresourcelocationextensionextension2coding['system']  = "http://terminology.kemkes.go.id/CodeSystem/locationUpgradeClass";

                        $encounterresourcelocationextensionextension1['url']                              = "value";
                        $encounterresourcelocationextensionextension1['valueCodeableConcept']['coding'][] = $encounterresourcelocationextensionextension1coding;
                        $encounterresourcelocationextensionextension2['url']                              = "upgradeClassIndicator";
                        $encounterresourcelocationextensionextension2['valueCodeableConcept']['coding'][] = $encounterresourcelocationextensionextension2coding;
                        
                        $encounterresourcelocationextensionextension[]=$encounterresourcelocationextensionextension1;
                        $encounterresourcelocationextensionextension[]=$encounterresourcelocationextensionextension2;

                        $encounterresourcelocationextension['extension']   = $encounterresourcelocationextensionextension;
                        $encounterresourcelocationextension['url']         = "https://fhir.kemkes.go.id/r4/StructureDefinition/ServiceClass";
                        $encounterresourcelocationlocation['display']      = $locationname;
                        $encounterresourcelocationlocation['reference']    = "Location/".$locationid;
                        $encounterresourcelocationperiod['end']            = $a->FINISH;
                        $encounterresourcelocationperiod['start']          = $a->PERIODSTART;
                        $encounterresourceparticipanttypecoding['code']    = "ATND";
                        $encounterresourceparticipanttypecoding['display'] = "attender";
                        $encounterresourceparticipanttypecoding['system']  = "http://terminology.hl7.org/CodeSystem/v3-ParticipationType";

                        $encounterresourceclass['code']                          = "AMB";
                        $encounterresourceclass['display']                       = "ambulatory";
                        $encounterresourceclass['system']                        = "http://terminology.hl7.org/CodeSystem/v3-ActCode";
                        $encounterresourceidentifier['system']                   = "http://sys-ids.kemkes.go.id/encounter/".RS_ID;
                        $encounterresourceidentifier['value']                    = $episodeid;
                        $encounterresourcelocation['extension'][]                = $encounterresourcelocationextension;
                        $encounterresourcelocation['location']                   = $encounterresourcelocationlocation;
                        $encounterresourcelocation['period']                     = $encounterresourcelocationperiod;
                        $encounterresourceparticipant['individual']['display']   = $practitionername;
                        $encounterresourceparticipant['individual']['reference'] = "Practitioner/".$practitionerid;
                        $encounterresourceparticipant['type'][]['coding'][]      = $encounterresourceparticipanttypecoding;

                        $encounterresource['class']                        = $encounterresourceclass;
                        $encounterresource['diagnosis']                    = $diagnosis;
                        $encounterresource['identifier'][]                 = $encounterresourceidentifier;
                        $encounterresource['location'][]                   = $encounterresourcelocation;
                        $encounterresource['participant'][]                = $encounterresourceparticipant;
                        $encounterresource['period']['end']                = $a->FINISH;
                        $encounterresource['period']['start']              = $a->PERIODSTART;
                        $encounterresource['resourceType']                 = "Encounter";
                        $encounterresource['serviceProvider']['reference'] = "Organization/".RS_ID;
                        $encounterresource['status']                       = "finished";
                        if($a->PERIODSTART!=null){
                            $statusHistoryperiodstart['period']['start'] = $a->PERIODSTART;
                            $statusHistoryperiodstart['period']['end']   = $a->PERIODSTART;
                            $statusHistoryperiodstart['status']          = "arrived";
                            $encounterresource['statusHistory'][]        = $statusHistoryperiodstart;
                        }
                        if($a->TRIAGE!=null){
                            $statusHistorytriage['period']['start'] = $a->TRIAGE;
                            $statusHistorytriage['period']['end']   = $a->TRIAGE;
                            $statusHistorytriage['status']          = "triaged";
                            $encounterresource['statusHistory'][]   = $statusHistorytriage;
                        }
                        if($a->FINISH!=null){
                            $statusHistoryfinish['period']['start'] = $a->FINISH;
                            $statusHistoryfinish['period']['end']   = $a->FINISH;
                            $statusHistoryfinish['status']          = "finished";
                            $encounterresource['statusHistory'][]   = $statusHistoryfinish;
                        }
                        $encounterresource['subject']['display']   = $patientname;
                        $encounterresource['subject']['reference'] = "Patient/".$patientid;

                        $encounter['fullUrl']           = "urn:uuid:".$uuidencounter;
                        $encounter['request']['method'] = "POST";
                        $encounter['request']['url']    = "Encounter";
                        $encounter['resource']          = $encounterresource;
                        
                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $encounter;
                        $body['entry']        = array_merge($body['entry'], $conditiondiag);

                        $response = Satusehat::postbundle(json_encode($body),$this->oauth['access_token']);

                        if($response === null){
                            echo formatlogbundle($pasienid,$episodeid,"Encounter","********", "ERROR | response | NULL response from SATUSEHAT","red");
                        }else{
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
                                if(isset($response['issue']) && is_array($response['issue']) && count($response['issue']) > 0){
                                    if($response['issue'][0]['code']==="duplicate"){
                                        $responsegetencounter = [];
                                        $responsegetencounter = Satusehat::getdata("Encounter","identifier",$episodeid,$this->oauth['access_token']);

                                        if(isset($responsegetencounter['entry'])){
                                            foreach($responsegetencounter['entry'] as $responsegetencounters){
                                                $simpanlog = [];

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
                                                $simpanlog['JENIS']         = "2";
                                                $simpanlog['ENVIRONMENT']   = SERVER;
                                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                    
                                                $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetencounters['resource']['resourceType'],$responsegetencounters['resource']['id']);

                                                if(empty($resultcekdataresouce)){
                                                    $this->mss->insertdata($simpanlog);
                                                }
                                                
                                                $statusColor = "yellow";
                                                $statusMsg   = "Duplicate | Encounter | Success Save Log Transaction";
                                                echo formatlogbundle($pasienid,$episodeid,$responsegetencounters['resource']['resourceType'],$responsegetencounters['resource']['id'], $statusMsg,$statusColor);
                                            }
                                        }

                                        $responsegetcondition = [];
                                        $responsegetcondition = Satusehat::getdata("Condition","identifier",$episodeid,$this->oauth['access_token']);

                                        if(isset($responsegetcondition['entry'])){
                                            foreach($responsegetcondition['entry'] as $responsegetconditions){
                                                $simpanlog = [];

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
                                                $simpanlog['JENIS']         = "2";
                                                $simpanlog['ENVIRONMENT']   = SERVER;
                                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                    
                                                $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetconditions['resource']['resourceType'],$responsegetconditions['resource']['id']);

                                                if(empty($resultcekdataresouce)){
                                                    $this->mss->insertdata($simpanlog);
                                                }
                                                
                                                $statusColor = "yellow";
                                                $statusMsg   = "Duplicate | Condition | Success Save Log Transaction";
                                                echo formatlogbundle($pasienid,$episodeid,$responsegetconditions['resource']['resourceType'],$responsegetconditions['resource']['id'], $statusMsg,$statusColor);
                                            }
                                        }

                                    }
                                }

                                if($response['issue'][0]['code']==="value"){
                                    $statusColor = "red";
                                    $statusMsg   = $response['issue'][0]['details']['text'];
                                    echo formatlogbundle($pasienid,$episodeid,$response['issue'][0]['expression'][0],"********", $statusMsg,$statusColor);
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