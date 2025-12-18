<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    date_default_timezone_set('Asia/Jakarta');
    use Restserver\Libraries\REST_Controller;
    require APPPATH . '/libraries/REST_Controller.php';

    class Procedure extends REST_Controller{
        public static $oauth;

        public function __construct(){
            parent::__construct();
            $this->load->model("ModelProcedure", "md");
            $this->load->model("ModelEncounter", "me");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
        }

        public function Tindakanrj_post(){
            if(!isset(self::$oauth['issue'])){
                $finalresponse = [];
                $result        = $this->md->TindakanRJ(SERVER);

                if(!empty($result)){
                    foreach ($result as $a){
                        $body                              = [];
                        $procedure                         = [];
                        $procedureresource                 = [];
                        $procedureresourcecategorycoding   = [];
                        $procedureresourcecodecoding       = [];
                        $procedureresourceencounter        = [];
                        $procedureresourceidentifier       = [];
                        $procedureresourceperformedperiod  = [];
                        $procedureresourceperformeractor   = [];
                        $procedureresourcereasoncode       = [];
                        $procedureresourcereasoncodecoding = [];
                        $procedureresourcesubject          = [];


                        $pasienid  = "";
                        $episodeid = "";
                        $poliid    = "";
                        $layanid   = "";
                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLIID;
                        $layanid   = $a->LAYAN_ID;

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


                        $diagnosa = "";
                        $diagnosa = $this->me->condition($episodeid);
                        foreach ($diagnosa as $diag){
                            $procedureresourcereasoncodecodingpost = [];
    
                            $procedureresourcereasoncodecodingpost['code']    = $diag->KODEDIAGNOSA;
                            $procedureresourcereasoncodecodingpost['display'] = $diag->DIAGNOSA;
                            $procedureresourcereasoncodecodingpost['system']  = "http://hl7.org/fhir/sid/icd-10";
    
                            $procedureresourcereasoncodecoding[] = $procedureresourcereasoncodecodingpost;
                        }
                        
                        $procedureresourcecategorycoding['code']      = $a->SNOMEDCTID;
                        $procedureresourcecategorycoding['display']   = $a->SNOMEDDISPLAY;
                        $procedureresourcecategorycoding['system']    = "http://snomed.info/sct";
                        $procedureresourcecodecoding['code']          = $a->CODEICDIX;
                        $procedureresourcecodecoding['display']       = $a->DESCICDIX;
                        $procedureresourcecodecoding['system']        = "http://hl7.org/fhir/sid/icd-9-cm";
                        $procedureresourceencounter['display']        = "Kunjungan Rawat Jalan Medical Record ".$mrpas.' Atasnama '.$patientname;
                        $procedureresourceencounter['reference']      = "Encounter/".$a->RESOURCEID;
                        $procedureresourceidentifier['system']        = "http://sys-ids.kemkes.go.id/procedure/".RS_ID;
                        $procedureresourceidentifier['use']           = "official";
                        $procedureresourceidentifier['value']         = $a->TRANS_CO;
                        $procedureresourceperformedperiod['end']      = $a->CREATED_DATE;
                        $procedureresourceperformedperiod['start']    = $a->CREATED_DATE;
                        $procedureresourceperformeractor['display']   = $practitionername;
                        $procedureresourceperformeractor['reference'] = "Practitioner/".$practitionerid;
                        $procedureresourcereasoncode['coding']        = $procedureresourcereasoncodecoding;
                        $procedureresourcesubject['display']          = $patientname;
                        $procedureresourcesubject['reference']        = "Patient/".$patientid;

                        $procedureresource['category']['coding'][] = $procedureresourcecategorycoding;
                        $procedureresource['category']['text']     = $a->SNOMEDDISPLAY;
                        $procedureresource['code']['coding'][]     = $procedureresourcecodecoding;
                        $procedureresource['encounter']            = $procedureresourceencounter;
                        $procedureresource['identifier'][]         = $procedureresourceidentifier;
                        $procedureresource['performedPeriod']      = $procedureresourceperformedperiod;
                        $procedureresource['performer'][]['actor'] = $procedureresourceperformeractor;
                        $procedureresource['reasonCode'][]         = $procedureresourcereasoncode;
                        $procedureresource['resourceType']         = "Procedure";
                        $procedureresource['status']               = "completed";
                        $procedureresource['subject']              = $procedureresourcesubject;

                        $procedure['fullUrl']           = "urn:uuid:".Satusehat::uuid();
                        $procedure['request']['method'] = "POST";
                        $procedure['request']['url']    = "Procedure";
                        $procedure['resource']          = $procedureresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $procedure;

                        // return $this->response($body);
                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);
                        if(isset($response['entry'])){
                            foreach($response['entry'] as $a){
                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['LAYAN_ID']      = $layanid;
                                $simpanlog['SNOMED_ID']     = "";
                                $simpanlog['LOCATION']      = $a['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $a['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $a['response']['resourceID'];
                                $simpanlog['ETAG']          = $a['response']['etag'];
                                $simpanlog['STATUS']        = $a['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $a['response']['lastModified'];
                                $simpanlog['JENIS']          = "5";
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
    
                                $this->mss->insertdata($simpanlog);
                                $finalresponse [] = $a;
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

    }

?>