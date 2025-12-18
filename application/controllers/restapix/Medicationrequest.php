<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    date_default_timezone_set('Asia/Jakarta');
    use Restserver\Libraries\REST_Controller;
    require APPPATH . '/libraries/REST_Controller.php';

    class Medicationrequest extends REST_Controller{
        public static $oauth;

        public function __construct(){
            parent::__construct();
            $this->load->model("Modelmedicationrequest", "md");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
        }

        public function Singledose_post(){
            if(!isset(self::$oauth['issue'])){
                $finalresponse = [];
                $result        = $this->md->medicationsingledose(SERVER);

                if(!empty($result)){
                    foreach ($result as $a){
                        $body                                                            = [];
                        $medication                                                      = [];
                        $medicationrequest                                               = [];
                        $medicationrequestresource                                       = [];
                        $medicationrequestresourcecategorycoding                         = [];
                        $medicationrequestresourcedispenseRequest                        = [];
                        $medicationrequestresourcedosageInstructiondoseAndRate           = [];
                        $medicationrequestresourcedosageInstructiondoseAndRatetypecoding = [];
                        $medicationrequestresourcedosageInstructionroutecoding           = [];
                        $medicationrequestresourceidentifier1                            = [];
                        $medicationrequestresourceidentifier2                            = [];
                        $medicationrequestresourceidentifier3                            = [];
                        $medicationrequestresourcemedicationReference                    = [];
                        $medicationrequestresourcerequester                              = [];
                        $medicationrequestresourcesubject                                = [];

                        $medication = [];
                        $medicationresource = [];
                        $medicationresourcecodecoding = [];
                        $medicationresourcextension = [];
                        $medicationresourceformcoding = [];
                        $medicationresourceidentifier1 = [];
                        $medicationresourceidentifier2 = [];
                        $medicationresourceidentifier3 = [];
                        $medicationresourcecodecoding = [];
                        $medicationresourcextension = [];
                        $medicationresourcextensionvalueCodeableConceptcoding = [];

                        $pasienid  = "";
                        $episodeid = "";
                        $transco   = "";
                        $obatid    = "";
                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $transco   = $a->TRANS_CO;
                        $obatid    = $a->OBAT_ID;

                        if(SERVER === "production"){
                            $patientid        = $a->PATIENTID;
                            $mrpas            = $a->PATIENTMR;
                            $patientname      = $a->PATIENTNAME;
                            $practitionerid   = $a->PRACTITIONERID;
                            $practitionername = $a->PRACTITIONERNAME;
                            // $locationid       = $a->LOCATIONID;
                            // $locationname     = $a->LOCATIONNAME;
                        }else{
                            $resultgetRandomPatient      = Satusehat::getRandomPatient();
                            $resultgetRandomPractitioner = Satusehat::getRandomPractitioner();

                            $patientid        = $resultgetRandomPatient['ihs'];
                            $mrpas            = "123456";
                            $patientname      = $resultgetRandomPatient['nama'];
                            $practitionerid   = $resultgetRandomPractitioner['ihs'];
                            $practitionername = $resultgetRandomPractitioner['nama'];
                            // $locationid       = "91b6b664-929e-4b67-802a-a0a86a607a0c";
                            // $locationname     = $a->LOCATIONNAME;
                        }

                        $uuidmedication = "";
                        $uuidmedication = Satusehat::uuid();

                        $medicationrequestresourcedosageInstructiondoseAndRatetypecoding['code']    = "ordered";
                        $medicationrequestresourcedosageInstructiondoseAndRatetypecoding['display'] = "Ordered";
                        $medicationrequestresourcedosageInstructiondoseAndRatetypecoding['system']  = "http://terminology.hl7.org/CodeSystem/dose-rate-type";

                        $medicationrequestresourcedosageInstructiondoseAndRate['type']['coding'][] = $medicationrequestresourcedosageInstructiondoseAndRatetypecoding;
                        $medicationrequestresourcedosageInstructionroutecoding['code']             = $a->ROUTECODE;
                        $medicationrequestresourcedosageInstructionroutecoding['display']          = $a->ROUTENAME;
                        $medicationrequestresourcedosageInstructionroutecoding['system']           = "http://www.whocc.no/atc";

                        $medicationrequestresourcecategorycoding['code']                               = "outpatient";
                        $medicationrequestresourcecategorycoding['display']                            = "Outpatient";
                        $medicationrequestresourcecategorycoding['system']                             = "http://terminology.hl7.org/CodeSystem/medicationrequest-category";
                        $medicationrequestresourcedispenseRequest['performer']['reference']            = "Organization/".RS_ID;
                        $medicationrequestresourcedosageInstruction['additionalInstruction'][]['text'] = $a->SIGNA_DOKTER." / ".$a->CATATAN;
                        $medicationrequestresourcedosageInstruction['doseAndRate'][]                   = $medicationrequestresourcedosageInstructiondoseAndRate;
                        $medicationrequestresourcedosageInstruction['patientInstruction']              = $a->SIGNA_DOKTER." / ".$a->CATATAN;
                        $medicationrequestresourcedosageInstruction['route']['coding'][]               = $medicationrequestresourcedosageInstructionroutecoding;
                        $medicationrequestresourcedosageInstruction['text']                            = $a->SIGNA_DOKTER." / ".$a->CATATAN;
                        $medicationrequestresourceidentifier1['system']                                = "http://sys-ids.kemkes.go.id/prescription/".RS_ID;
                        $medicationrequestresourceidentifier1['use']                                   = "official";
                        $medicationrequestresourceidentifier1['value']                                 = $episodeid;
                        $medicationrequestresourceidentifier2['system']                                = "http://sys-ids.kemkes.go.id/prescription-item/".RS_ID;
                        $medicationrequestresourceidentifier2['use']                                   = "official";
                        $medicationrequestresourceidentifier2['value']                                 = $transco;
                        $medicationrequestresourceidentifier3['system']                                = "http://sys-ids.kemkes.go.id/prescription-item/".RS_ID;
                        $medicationrequestresourceidentifier3['use']                                   = "official";
                        $medicationrequestresourceidentifier3['value']                                 = $obatid."-".$a->NOURUT;
                        $medicationrequestresourcemedicationReference['display']                       = $a->POANAME;
                        $medicationrequestresourcemedicationReference['reference']                     = "urn:uuid:".$uuidmedication;
                        $medicationrequestresourcerequester['display']                                 = $practitionername;
                        $medicationrequestresourcerequester['reference']                               = "Practitioner/".$practitionerid;
                        $medicationrequestresourcesubject['display']                                   = $patientname;
                        $medicationrequestresourcesubject['reference']                                 = "Patient/".$patientid;


                        $medicationrequestresource['authoredOn']             = $a->TGLORDER;
                        $medicationrequestresource['category'][]['coding'][] = $medicationrequestresourcecategorycoding;
                        $medicationrequestresource['dispenseRequest']        = $medicationrequestresourcedispenseRequest;
                        $medicationrequestresource['dosageInstruction'][]    = $medicationrequestresourcedosageInstruction;
                        $medicationrequestresource['encounter']['reference'] = "Encounter/".$a->ENCOUNTERID;
                        $medicationrequestresource['identifier'][]           = $medicationrequestresourceidentifier1;
                        $medicationrequestresource['identifier'][]           = $medicationrequestresourceidentifier2;
                        $medicationrequestresource['identifier'][]           = $medicationrequestresourceidentifier3;
                        $medicationrequestresource['intent']                 = "order";
                        $medicationrequestresource['medicationReference']    = $medicationrequestresourcemedicationReference;
                        $medicationrequestresource['priority']               = "routine";
                        $medicationrequestresource['requester']              = $medicationrequestresourcerequester;
                        $medicationrequestresource['resourceType']           = "MedicationRequest";
                        $medicationrequestresource['status']                 = "completed";
                        $medicationrequestresource['subject']                = $medicationrequestresourcesubject;
                        
                        $medicationrequest['fullUrl']           = "urn:uuid:".Satusehat::uuid();
                        $medicationrequest['request']['method'] = "POST";
                        $medicationrequest['request']['url']    = "MedicationRequest";
                        $medicationrequest['resource']          = $medicationrequestresource;

                        ////////////////////////////////////////////////////////////////////////

                        $medicationresourcextensionvalueCodeableConceptcoding['code']    = "NC";
                        $medicationresourcextensionvalueCodeableConceptcoding['display'] = "Non-compound";
                        $medicationresourcextensionvalueCodeableConceptcoding['system']  = "http://terminology.kemkes.go.id/CodeSystem/medication-type";

                        $medicationresourcecodecoding['code']                           = $a->KFAID;
                        $medicationresourcecodecoding['display']                        = $a->POANAME;
                        $medicationresourcecodecoding['system']                         = "http://sys-ids.kemkes.go.id/kfa";
                        $medicationresourcextension['url']                              = "https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType";
                        $medicationresourcextension['valueCodeableConcept']['coding'][] = $medicationresourcextensionvalueCodeableConceptcoding;
                        $medicationresourceformcoding['code']                           = $a->FORMCODE;
                        $medicationresourceformcoding['display']                        = $a->FORMNAME;
                        $medicationresourceformcoding['system']                         = "http://terminology.kemkes.go.id/CodeSystem/medication-form";
                        $medicationresourceidentifier1['system']                        = "http://sys-ids.kemkes.go.id/medication/".RS_ID;
                        $medicationresourceidentifier1['use']                           = "official";
                        $medicationresourceidentifier1['value']                         = $episodeid;
                        $medicationresourceidentifier2['system']                        = "http://sys-ids.kemkes.go.id/medication/".RS_ID;
                        $medicationresourceidentifier2['use']                           = "official";
                        $medicationresourceidentifier2['value']                         = $transco;
                        $medicationresourceidentifier3['system']                        = "http://sys-ids.kemkes.go.id/medication/".RS_ID;
                        $medicationresourceidentifier3['use']                           = "official";
                        $medicationresourceidentifier3['value']                         = $obatid;

                        $medicationresource['code']['coding'][] = $medicationresourcecodecoding;
                        $medicationresource['extension'][]      = $medicationresourcextension;
                        $medicationresource['form']['coding'][] = $medicationresourceformcoding;
                        $medicationresource['identifier'][]     = $medicationresourceidentifier1;
                        $medicationresource['identifier'][]     = $medicationresourceidentifier2;
                        $medicationresource['identifier'][]     = $medicationresourceidentifier3;
                        $medicationresource['resourceType']     = "Medication";
                        $medicationresource['status']           = "active";

                        $medication['fullUrl']                  = "urn:uuid:".$uuidmedication;
                        $medication['request']['method']        = "POST";
                        $medication['request']['url']           = "Medication";
                        $medication['resource']                 = $medicationresource;
                        
                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $medication;
                        $body['entry'][]      = $medicationrequest;

                        // return $this->response($body);
                        $response = Satusehat::postbundle(json_encode($body),self::$oauth['access_token']);
                        if(isset($response['entry'])){
                            foreach($response['entry'] as $a){
                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['OBAT_ID']       = $obatid;
                                $simpanlog['LOCATION']      = $a['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $a['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $a['response']['resourceID'];
                                $simpanlog['ETAG']          = $a['response']['etag'];
                                $simpanlog['STATUS']        = $a['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $a['response']['lastModified'];
                                $simpanlog['NOTE']          = "REQUEST";
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
    
                                $this->mss->insertdatamedication($simpanlog);
                            } 
                        }else{
                            $responseMedicationRequest = Satusehat::getdata("MedicationRequest","identifier",$episodeid,self::$oauth['access_token']);
                            // return $this->response($responseMedicationRequest);
                            foreach($responseMedicationRequest['entry'] as $a){
                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['OBAT_ID']       = $obatid;
                                $simpanlog['LOCATION']      = $a['fullUrl'];
                                $simpanlog['RESOURCE_TYPE'] = $a['resource']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $a['resource']['id'];
                                $simpanlog['ETAG']          = $a['resource']['meta']['versionId'];
                                $simpanlog['STATUS']        = "201 Created";
                                $simpanlog['LAST_MODIFIED'] = $a['resource']['meta']['lastUpdated'];
                                $simpanlog['NOTE']          = "REQUEST";
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
    
                                $this->mss->insertdatamedication($simpanlog);

                                $responseMedication = Satusehat::getdataid("Medication",str_replace("Medication/", "", $a['resource']['medicationReference']['reference']),self::$oauth['access_token']);
                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['OBAT_ID']       = $obatid;
                                $simpanlog['RESOURCE_TYPE'] = $responseMedication['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $responseMedication['id'];
                                $simpanlog['ETAG']          = $responseMedication['meta']['versionId'];
                                $simpanlog['STATUS']        = "201 Created";
                                $simpanlog['LAST_MODIFIED'] = $responseMedication['meta']['lastUpdated'];
                                $simpanlog['NOTE']          = "REQUEST";
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                $this->mss->insertdatamedication($simpanlog);
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