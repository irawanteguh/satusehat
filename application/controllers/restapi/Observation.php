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
    
    class Observation extends MY_Satusehat_Controller{

        public function __construct(){
            parent::__construct();
            $this->load->model("Modelobservation", "md");
        }

        
        public function psikologisigd_post(){
            if(!isset(self::$oauth['issue'])){

                $result = $this->md->psikologisIGD(SERVER);

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
                        $dokterid            = "";
                        $transid             = "";

                        $body                                          = [];
                        $psiokologis                                   = [];
                        $psiokologisresource                           = [];
                        $psiokologisresourcecategorycoding             = [];
                        $psiokologisresourcecodecoding                 = [];
                        $encounter                                     = [];
                        $performer                                     = [];
                        $subject                                       = [];
                        $identifier                                    = [];
                        $psiokologisresourcevalueCodeableConceptcoding = [];

                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLI_ID;
                        $dokterid  = $a->DOKTER_ID;
                        $transid   = $a->TRANS_ID;

                        if(SERVER === "production"){
                            $patientid           = $a->PATIENTID;
                            $mrpas               = $a->PATIENTMR;
                            $patientname         = $a->PATIENTNAME;
                            $practitionerid      = $a->PRACTITIONERID;
                            $practitionername    = $a->PRACTITIONERNAME;
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
                            $locationid          = "91b6b664-929e-4b67-802a-a0a86a607a0c";
                            $locationname        = $a->LOCATIONNAME;
                        }

                        $psiokologisresourcecategorycoding['code']    = "survey";
                        $psiokologisresourcecategorycoding['display'] = "Survey";
                        $psiokologisresourcecategorycoding['system']  = "http://terminology.hl7.org/CodeSystem/observation-category";
                        $psiokologisresourcecodecoding['code']        = "8693-4";
                        $psiokologisresourcecodecoding['display']     = "Mental Status";
                        $psiokologisresourcecodecoding['system']      = "http://loinc.org";
                        $encounter['display']                         = "Kunjungan Instalasi Gawat Darurat Medical Record ".$mrpas.' Atasnama '.$patientname;
                        $encounter['reference']                       = "Encounter/".$a->RESOURCE_ID;
                        $performer['display']                         = $practitionername;
                        $performer['reference']                       = "Practitioner/".$practitionerid;
                        $subject['display']                           = $patientname;
                        $subject['reference']                         = "Patient/".$patientid;
                        $identifier['system']                         = "http://sys-ids.kemkes.go.id/observation/".RS_ID;
                        $identifier['use']                            = "official";
                        $identifier['value']                          = $transid;
                        $psikologiMapping                             = ['0' => ['386661006', 'Angry'], '1' => ['48694002', 'Feeling anxious'], '2' => ['48694002', 'Feeling anxious'], '3' => ['35489007', 'Depressive disorder'], '4' => ['48694002', 'Feeling anxious'], '5' => [' suicidal', 'Suicidal tendency'], '6' => ['17326005', 'Normal mental state']]; [$psiokologisresourcevalueCodeableConceptcoding['code'], $psiokologisresourcevalueCodeableConceptcoding['display']] = $psikologiMapping[(string) $a->PSIKOLOGI] ?? ['', '']; $psiokologisresourcevalueCodeableConceptcoding['system'] = "http://snomed.info/sct";

                        $psiokologisresource['category'][]['coding'][]           = $psiokologisresourcecategorycoding;
                        $psiokologisresource['code']['coding'][]                 = $psiokologisresourcecodecoding;
                        $psiokologisresource['effectiveDateTime']                = $a->PERIODSTART;
                        $psiokologisresource['encounter']                        = $encounter;
                        $psiokologisresource['issued']                           = $a->PERIODSTART;
                        $psiokologisresource['performer'][]                      = $performer;
                        $psiokologisresource['resourceType']                     = "Observation";
                        $psiokologisresource['status']                           = "final";
                        $psiokologisresource['subject']                          = $subject;
                        $psiokologisresource['identifier'][]                     = $identifier;
                        $psiokologisresource['valueCodeableConcept']['coding'][] = $psiokologisresourcevalueCodeableConceptcoding;

                        $psiokologis['fullUrl']           = "urn:uuid:".Satusehat::uuid();
                        $psiokologis['request']['method'] = "POST";
                        $psiokologis['request']['url']    = "Observation";
                        $psiokologis['resource']          = $psiokologisresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $psiokologis;

                        $response = Satusehat::postbundle(json_encode($body),$this->oauth['access_token']);

                        if($response === null){
                            echo formatlogbundle($pasienid,$episodeid,"Observation","********", "ERROR | response | NULL response from SATUSEHAT","red");
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
                                    $simpanlog['JENIS']         = "4";
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
                                        $responsegetencounter = Satusehat::getdata("Observation","identifier",$episodeid,$this->oauth['access_token']);

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
                                                $simpanlog['JENIS']         = "4";
                                                $simpanlog['ENVIRONMENT']   = SERVER;
                                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                    
                                                $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetencounters['resource']['resourceType'],$responsegetencounters['resource']['id']);

                                                if(empty($resultcekdataresouce)){
                                                    $this->mss->insertdata($simpanlog);
                                                }
                                                
                                                $statusColor = "yellow";
                                                $statusMsg   = "Duplicate | Observation | Success Save Log Transaction";
                                                echo formatlogbundle($pasienid,$episodeid,$responsegetencounters['resource']['resourceType'],$responsegetencounters['resource']['id'], $statusMsg,$statusColor);
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