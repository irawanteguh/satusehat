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
    
    class Clinicalimpression extends MY_Satusehat_Controller{

        public function __construct(){
            parent::__construct();
            $this->load->model("Modelclinicalimpression", "md");
        }

        public function clinicalimpressionrjigd_post(){
            if(!isset(self::$oauth['issue'])){

                $result = $this->md->clinicalimpressionRJIGD(SERVER);

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

                        $body                               = [];
                        $clinicalimpression                 = [];
                        $clinicalimpressionresource         = [];
                        $clinicalimpressionresourceassessor = [];
                        $encounter                          = [];
                        $identifier                         = [];
                        $clinicalimpressionresourceproblem  = [];
                        $conditions                         = [];
                        $subject                            = [];
                        
                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLI_ID;
                        $dokterid  = $a->DOKTER_ID;
                        $transid   = $a->EPISODE_ID;

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

                        $clinicalimpressionresourceassessor['display']   = $practitionername;
                        $clinicalimpressionresourceassessor['reference'] = "Practitioner/".$practitionerid;
                        $encounter['display']                = "Kunjungan OutPatient Medical Record ".$mrpas.' Atasnama '.$patientname;
                        $encounter['reference']              = "Encounter/".$a->RESOURCE_ID;
                        $identifier['system']                = "http://sys-ids.kemkes.go.id/observation/".RS_ID;
                        $identifier['use']                   = "official";
                        $identifier['value']                 = $transid;
                        $subject['display']                  = $patientname;
                        $subject['reference']                = "Patient/".$patientid;

                        $conditions = explode(',', $a->CONDITION);
                        foreach ($conditions as $conditionId) {
                            $refcondition              = [];
                            $conditionId               = trim($conditionId);
                            $refcondition['reference'] = "Condition/".$conditionId;

                            $clinicalimpressionresourceproblem[] = $refcondition;
                        }
                        

                        $clinicalimpressionresource['assessor']          = $clinicalimpressionresourceassessor;
                        $clinicalimpressionresource['date']              = $a->PERIODSTART;
                        $clinicalimpressionresource['description']       = $a->ASSESSMENT;
                        $clinicalimpressionresource['effectiveDateTime'] = $a->PERIODSTART;
                        $clinicalimpressionresource['encounter']         = $encounter;
                        $clinicalimpressionresource['identifier'][]      = $identifier;
                        $clinicalimpressionresource['summary']           = $a->ASSESSMENT;
                        $clinicalimpressionresource['problem']           = $clinicalimpressionresourceproblem;
                        $clinicalimpressionresource['resourceType']      = "ClinicalImpression";
                        $clinicalimpressionresource['status']            = "completed";
                        $clinicalimpressionresource['subject']           = $subject;

                        $clinicalimpression['fullUrl']           = "urn:uuid:".Satusehat::uuid();
                        $clinicalimpression['request']['method'] = "POST";
                        $clinicalimpression['request']['url']    = "ClinicalImpression";
                        $clinicalimpression['resource']          = $clinicalimpressionresource;
                        
                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $clinicalimpression;

                        $response = Satusehat::postbundle(json_encode($body),$this->oauth['access_token']);


                        if($response === null){
                            echo formatlogbundle($pasienid,$episodeid,"ClinicalImpression","********", "ERROR | response | NULL response from SATUSEHAT","red");
                        }else{
                            if(isset($response['entry'])){
                                foreach($response['entry'] as $entrys){
                                    $simpanlog = [];

                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                    $simpanlog['POLI_ID']       = $poliid;
                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                    $simpanlog['IDENTIFIER']    = $transid;
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
                                        $responsegetencounter = Satusehat::getdata("CarePlan","identifier",$transid,$this->oauth['access_token']);

                                        if(isset($responsegetencounter['entry'])){
                                            foreach($responsegetencounter['entry'] as $responsegetencounters){
                                                $simpanlog = [];

                                                $simpanlog['PASIEN_ID']     = $pasienid;
                                                $simpanlog['EPISODE_ID']    = $episodeid;
                                                $simpanlog['POLI_ID']       = $poliid;
                                                $simpanlog['DOKTER_ID']     = $dokterid;
                                                $simpanlog['IDENTIFIER']    = $transid;
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
                                                $statusMsg   = "Duplicate | ClinicalImpression | Success Save Log Transaction";
                                                echo formatlogbundle($pasienid,$episodeid,$responsegetencounters['resource']['resourceType'],$responsegetencounters['resource']['id'], $statusMsg,$statusColor);
                                            }
                                        }
                                    }else{
                                        $statusColor = "red";
                                        $statusMsg   = $response['issue'][0]['details']['text'];
                                        echo formatlogbundle($pasienid,$episodeid,"ClinicalImpression","********", $statusMsg,$statusColor);
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