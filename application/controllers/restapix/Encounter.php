<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    date_default_timezone_set('Asia/Jakarta');
    use Restserver\Libraries\REST_Controller;
    require APPPATH . '/libraries/REST_Controller.php';

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
        }

        public function Rawatjalan_post(){
            if(!isset(self::$oauth['issue'])){
                $finalresponse = [];
                $result        = $this->md->EncounterRJ(SERVER);

                if(!empty($result)){
                    foreach ($result as $a){
                        $body                                   = [];
                        $encounter                              = [];
                        $encounterresource                      = [];
                        $encounterresourceclass                 = [];
                        $encounterresourceidentifier            = [];
                        $encounterresourcelocation              = [];
                        $encounterresourceparticipant           = [];
                        $encounterresourceparticipanttypecoding = [];
                        $encounterresourceperiod                = [];
                        $statusHistoryfinish                    = [];
                        $statusHistoryinprogress                = [];
                        $statusHistorytriage                    = [];
                        $statusHistoryperiodstart               = [];
                        $statusHistoryplanned                   = [];
                        $encounterresourcesubject               = [];

                        $uuidecounter = "";
                        $uuidecounter = Satusehat::uuid();

                        $pasienid  = "";
                        $episodeid = "";
                        $poliid    = "";
                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLI_ID;

                        $patientid        = "";
                        $mrpas            = "";
                        $patientname      = "";
                        $practitionerid   = "";
                        $practitionername = "";
                        $locationid       = "";
                        $locationname     = "";

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

                            $patientid        = $resultgetRandomPatient['ihs'];
                            $mrpas            = "123456";
                            $patientname      = $resultgetRandomPatient['nama'];
                            $practitionerid   = $resultgetRandomPractitioner['ihs'];
                            $practitionername = $resultgetRandomPractitioner['nama'];
                            $locationid       = "91b6b664-929e-4b67-802a-a0a86a607a0c";
                            $locationname     = $a->LOCATIONNAME;
                        }

                        $condition = $this->Condition($episodeid,$mrpas,$patientname,$uuidecounter,$a->INPROGRESSEND,$patientid);

                        $encounterresourceparticipanttypecoding['code']    = "ATND";
                        $encounterresourceparticipanttypecoding['display'] = "attender";
                        $encounterresourceparticipanttypecoding['system']  = "http://terminology.hl7.org/CodeSystem/v3-ParticipationType";
                        /////////////////////////////////////////////////////////////
                        $encounterresourceclass['code']                          = "AMB";
                        $encounterresourceclass['display']                       = "ambulatory";
                        $encounterresourceclass['system']                        = "http://terminology.hl7.org/CodeSystem/v3-ActCode";
                        $encounterresourceidentifier['system']                   = "http://sys-ids.kemkes.go.id/encounter/".RS_ID;
                        $encounterresourceidentifier['use']                      = "official";
                        $encounterresourceidentifier['value']                    = $episodeid;
                        $encounterresourcelocation['location']['reference']      = "Location/".$locationid;
                        $encounterresourcelocation['location']['display']        = $locationname;
                        $encounterresourceparticipant['individual']['display']   = $practitionername;
                        $encounterresourceparticipant['individual']['reference'] = "Practitioner/".$practitionerid;
                        $encounterresourceparticipant['type'][]['coding'][]      = $encounterresourceparticipanttypecoding;
                        $encounterresourceperiod['start']                        = $a->PERIODSTART;
                        $encounterresourceperiod['end']                          = $a->FINISH;
                        $encounterresourcesubject['display']                     = $patientname;
                        $encounterresourcesubject['reference']                   = "Patient/".$patientid;
                        /////////////////////////////////////////////////////////////
                        $encounterresource['class']                        = $encounterresourceclass;
                        $encounterresource['diagnosis']                    = $condition['diagnosis'];
                        $encounterresource['identifier'][]                 = $encounterresourceidentifier;
                        $encounterresource['location'][]                   = $encounterresourcelocation;
                        $encounterresource['participant'][]                = $encounterresourceparticipant;
                        $encounterresource['period']                       = $encounterresourceperiod;
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
                        if($a->INPROGRESSSTART!=null && $a->INPROGRESSEND!=null){
                            $statusHistoryinprogress['period']['start'] = $a->INPROGRESSSTART;
                            $statusHistoryinprogress['period']['end']   = $a->INPROGRESSEND;
                            $statusHistoryinprogress['status']          = "in-progress";
                            $encounterresource['statusHistory'][]       = $statusHistoryinprogress;
                        }
                        if($a->FINISH!=null){
                            $statusHistoryfinish['period']['start'] = $a->FINISH;;
                            $statusHistoryfinish['period']['end']   = $a->FINISH;;
                            $statusHistoryfinish['status']          = "finished";
                            $encounterresource['statusHistory'][]   = $statusHistoryfinish;
                        }
                        $encounterresource['subject'] = $encounterresourcesubject;
                        /////////////////////////////////////////////////////////////
                        $encounter['fullUrl']           = "urn:uuid:".$uuidecounter;
                        $encounter['request']['method'] = "POST";
                        $encounter['request']['url']    = "Encounter";
                        $encounter['resource']          = $encounterresource;
                        /////////////////////////////////////////////////////////////
                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $encounter;
                        $body['entry']        = array_merge($body['entry'], $condition['condition']);

                        // return $this->response($body);
                        $response         = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);
                        return $this->response($response);

                        if(isset($response['entry'])){
                            foreach($response['entry'] as $a){
                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['LAYAN_ID']      = "";
                                $simpanlog['SNOMED_ID']     = "";
                                $simpanlog['LOCATION']      = $a['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $a['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $a['response']['resourceID'];
                                $simpanlog['ETAG']          = $a['response']['etag'];
                                $simpanlog['STATUS']        = $a['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $a['response']['lastModified'];
                                $simpanlog['JENIS']         = "";
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
    
                                $this->mss->insertdata($simpanlog);
                            } 
                        }else{
                            if(isset($response['issue'])){
                                if($response['issue'][0]['code']==="value" && $response['issue'][0]['details']['text']==="reference_not_found"){
                                    $responsegetpatientid = Satusehat::getpatientid($a->NO_IDENTITAS,self::$oauth['access_token']);
                                    if(isset($responsegetpatientid['entry'][0]['resource']['id'])){
                                        $data['SATUSEHAT_ID']=$responsegetpatientid['entry'][0]['resource']['id'];
                                        $this->md->updatedatapatient($a->PASIEN_ID,$data);
                                    }
                                }
                            }

                            if(isset($response['issue'])){
                                if($response['issue'][0]['code']==="duplicate"){
                                    $responseencounter = Satusehat::getdata("Encounter","identifier",$episodeid,self::$oauth['access_token']);
                                    foreach($responseencounter['entry'] as $a){
                                        $simpanlog['PASIEN_ID']     = $pasienid;
                                        $simpanlog['EPISODE_ID']    = $episodeid;
                                        $simpanlog['POLI_ID']       = $poliid;
                                        $simpanlog['LAYAN_ID']      = "";
                                        $simpanlog['SNOMED_ID']     = "";
                                        $simpanlog['LOCATION']      = $a['fullUrl'];
                                        $simpanlog['RESOURCE_TYPE'] = $a['resource']['resourceType'];
                                        $simpanlog['RESOURCE_ID']   = $a['resource']['id'];
                                        $simpanlog['ETAG']          = $a['resource']['meta']['versionId'];
                                        $simpanlog['STATUS']        = "201 Created";
                                        $simpanlog['LAST_MODIFIED'] = $a['resource']['meta']['lastUpdated'];
                                        $simpanlog['JENIS']         = "";
                                        $simpanlog['ENVIRONMENT']   = SERVER;
                                        $simpanlog['CREATED_BY']    = "MIDDLEWARE";
            
                                        $this->mss->insertdata($simpanlog);
                                    }

                                    $responsecondition = Satusehat::getdata("Condition","identifier",$episodeid,self::$oauth['access_token']); 
                                    foreach($responsecondition['entry'] as $a){
                                        $simpanlog['PASIEN_ID']     = $pasienid;
                                        $simpanlog['EPISODE_ID']    = $episodeid;
                                        $simpanlog['POLI_ID']       = $poliid;
                                        $simpanlog['LAYAN_ID']      = "";
                                        $simpanlog['SNOMED_ID']     = "";
                                        $simpanlog['LOCATION']      = $a['fullUrl'];
                                        $simpanlog['RESOURCE_TYPE'] = $a['resource']['resourceType'];
                                        $simpanlog['RESOURCE_ID']   = $a['resource']['id'];
                                        $simpanlog['ETAG']          = $a['resource']['meta']['versionId'];
                                        $simpanlog['STATUS']        = "201 Created";
                                        $simpanlog['LAST_MODIFIED'] = $a['resource']['meta']['lastUpdated'];
                                        $simpanlog['JENIS']         = "";
                                        $simpanlog['ENVIRONMENT']   = SERVER;
                                        $simpanlog['CREATED_BY']    = "MIDDLEWARE";
            
                                        $this->mss->insertdata($simpanlog);
                                    }
                                }
                            }
                        }

                        $finalresponse [] = $response;
                    }
                    $this->response($finalresponse,REST_Controller::HTTP_OK);
                }
            }else{
                $this->response(self::$oauth,REST_Controller::HTTP_OK);
            }
        }

        public function Condition($episodeid,$mrpas,$patientname,$uuidecounter,$jamselesai,$patientid){
            $response  = [];
            $diagnosis = [];
            $condition = [];
            $diagnosa  = "";
            $diagnosa  = $this->md->condition($episodeid);
        
            foreach ($diagnosa as $diag){
                $diagnosispost          = [];
                $diagnosispostcondition = [];
                $diagnosispostusecoding = [];
        
                $conditionpost                             = [];
                $conditionpostresource                     = [];
                $conditionpostresourcecategorycoding       = [];
                $conditionpostresourceclinicalStatuscoding = [];
                $conditionpostresourcecodecoding           = [];
                $conditionpostresourceencounter            = [];
                $conditionpostresourceidentifier           = [];
                $conditionpostresourcesubject              = [];
        
                $uuidcondition = "";
                $uuidcondition = Satusehat::uuid();
        
                $diagnosispostcondition['display']   = $diag->DIAGNOSA;
                $diagnosispostcondition['reference'] = "urn:uuid:".$uuidcondition;
                $diagnosispostusecoding['code']      = "DD";
                $diagnosispostusecoding['display']   = "Discharge diagnosis";
                $diagnosispostusecoding['system']    = "http://terminology.hl7.org/CodeSystem/diagnosis-role";
        
                $diagnosispost['condition']       = $diagnosispostcondition;
                $diagnosispost['rank']            = floatval($diag->RANK);
                $diagnosispost['use']['coding'][] = $diagnosispostusecoding;
        
                $diagnosis[] = $diagnosispost;
        
                //==============================================================
                $conditionpostresourcecategorycoding['code']          = "encounter-diagnosis";
                $conditionpostresourcecategorycoding['display']       = "Encounter Diagnosis";
                $conditionpostresourcecategorycoding['system']        = "http://terminology.hl7.org/CodeSystem/condition-category";
                $conditionpostresourceclinicalStatuscoding['code']    = "active";
                $conditionpostresourceclinicalStatuscoding['display'] = "Active";
                $conditionpostresourceclinicalStatuscoding['system']  = "http://terminology.hl7.org/CodeSystem/condition-clinical";
                $conditionpostresourcecodecoding['code']              = $diag->KODEDIAGNOSA;
                $conditionpostresourcecodecoding['display']           = $diag->DIAGNOSA;
                $conditionpostresourcecodecoding['system']            = "http://hl7.org/fhir/sid/icd-10";
                $conditionpostresourceencounter['display']            = "Kunjungan Rawat Jalan Medical Record ".$mrpas.' Atasnama '.$patientname;
                $conditionpostresourceencounter['reference']          = "urn:uuid:".$uuidecounter;
                $conditionpostresourceidentifier['system']            = "http://sys-ids.kemkes.go.id/condition/".RS_ID;
                $conditionpostresourceidentifier['use']               = "official";
                $conditionpostresourceidentifier['value']             = $episodeid;
                $conditionpostresourcesubject['display']              = $patientname;
                $conditionpostresourcesubject['reference']            = "Patient/".$patientid;

                $conditionpostresource['category'][]['coding'][]     = $conditionpostresourcecategorycoding;
                $conditionpostresource['clinicalStatus']['coding'][] = $conditionpostresourceclinicalStatuscoding;
                $conditionpostresource['code']['coding'][]           = $conditionpostresourcecodecoding;
                $conditionpostresource['encounter']                  = $conditionpostresourceencounter;
                $conditionpostresource['identifier'][]               = $conditionpostresourceidentifier;
                if($jamselesai!=null){
                    $conditionpostresource['onsetDateTime']              = $jamselesai;
                    $conditionpostresource['recordedDate']               = $jamselesai;
                }
                $conditionpostresource['resourceType']               = "Condition";
                $conditionpostresource['subject']                    = $conditionpostresourcesubject;

                $conditionpost['fullUrl']           = "urn:uuid:".$uuidcondition;
                $conditionpost['request']['method'] = "POST";
                $conditionpost['request']['url']    = "Condition";
                $conditionpost['resource']          = $conditionpostresource;
        
                $condition[] = $conditionpost;
            }
        
            $response['diagnosis']=$diagnosis;
            $response['condition']=$condition;
        
            return $response;
        }
    }

?>