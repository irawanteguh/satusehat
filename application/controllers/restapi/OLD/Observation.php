<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    date_default_timezone_set('Asia/Jakarta');
    require_once APPPATH . 'core/MY_Satusehat_Controller.php';

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

    class Observation extends MY_Satusehat_Controller{

        public function __construct(){
            parent::__construct();
            $this->load->model("Modelobservation", "md");
        }
        
        public function anamnesaawalrj_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->AnamnesaawalRJ(SERVER);
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

                        $body           = [];
                        $categorycoding = [];
                        $encounter      = [];
                        $identifier     = [];
                        $performer      = [];
                        $subject        = [];

                        $heart                         = [];
                        $heartresource                 = [];
                        $heartresourcecodecoding       = [];
                        $respiratory                   = [];
                        $respiratoryresource           = [];
                        $respiratoryresourcecodecoding = [];
                        $systolic                      = [];
                        $systolicresource              = [];
                        $systolicresourcecodecoding    = [];
                        $diastolic                     = [];
                        $diastolicresource             = [];
                        $diastolicresourcecodecoding   = [];
                        $temp                          = [];
                        $tempresource                  = [];
                        $tempresourcecodecoding        = [];
                        $beratbadan                    = [];
                        $beratbadanresource            = [];
                        $beratbadanresourcecodecoding  = [];
                        $tinggibadan                   = [];
                        $tinggibadanresource           = [];
                        $tinggibadanresourcecodecoding = [];
                        $imtscale                      = [];
                        $imtscaleresource              = [];
                        $imtscaleresourcecodecoding    = [];

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

                        $categorycoding['code']    = "vital-signs";
                        $categorycoding['display'] = "Vital Signs";
                        $categorycoding['system']  = "http://terminology.hl7.org/CodeSystem/observation-category";
                        $encounter['display']      = "Kunjungan Rawat Jalan Medical Record ".$mrpas.' Atasnama '.$patientname;
                        $encounter['reference']    = "Encounter/".$a->RESOURCE_ID;
                        $identifier['system']      = "http://sys-ids.kemkes.go.id/observation/".RS_ID;
                        $identifier['use']         = "official";
                        $identifier['value']       = $transid;
                        $performer['display']      = $practitionername;
                        $performer['reference']    = "Practitioner/".$practitionerid;
                        $subject['display']        = $patientname;
                        $subject['reference']      = "Patient/".$patientid;

                        $heartresourcecodecoding['code']          = "8867-4";
                        $heartresourcecodecoding['display']       = "Heart rate";
                        $heartresourcecodecoding['system']        = "http://loinc.org";
                        $heartresource['category'][]['coding'][]  = $categorycoding;
                        $heartresource['code']['coding'][]        = $heartresourcecodecoding;
                        $heartresource['effectiveDateTime']       = $a->TRIAGE;
                        $heartresource['encounter']               = $encounter;
                        $heartresource['identifier'][]            = $identifier;
                        $heartresource['issued']                  = $a->TRIAGE;
                        $heartresource['performer'][]             = $performer;
                        $heartresource['resourceType']            = "Observation";
                        $heartresource['status']                  = "final";
                        $heartresource['subject']                 = $subject;
                        $heartresource['valueQuantity']['code']   = "/min";
                        $heartresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $heartresource['valueQuantity']['unit']   = "breaths/minute";
                        $heartresource['valueQuantity']['value']  = floatval($a->TV_FREK_NADI);
                        $heart['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $heart['request']['method']               = "POST";
                        $heart['request']['url']                  = "Observation";
                        $heart['resource']                        = $heartresource;


                        $respiratoryresourcecodecoding['code']          = "9279-1";
                        $respiratoryresourcecodecoding['display']       = "Respiratory rate";
                        $respiratoryresourcecodecoding['system']        = "http://loinc.org";
                        $respiratoryresource['category'][]['coding'][]  = $categorycoding;
                        $respiratoryresource['code']['coding'][]        = $respiratoryresourcecodecoding;
                        $respiratoryresource['effectiveDateTime']       = $a->TRIAGE;
                        $respiratoryresource['encounter']               = $encounter;
                        $respiratoryresource['identifier'][]            = $identifier;
                        $respiratoryresource['issued']                  = $a->TRIAGE;
                        $respiratoryresource['performer'][]             = $performer;
                        $respiratoryresource['resourceType']            = "Observation";
                        $respiratoryresource['status']                  = "final";
                        $respiratoryresource['subject']                 = $subject;
                        $respiratoryresource['valueQuantity']['code']   = "/min";
                        $respiratoryresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $respiratoryresource['valueQuantity']['unit']   = "beats/minute";
                        $respiratoryresource['valueQuantity']['value']  = floatval($a->TV_FREK_NAFAS);
                        $respiratory['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $respiratory['request']['method']               = "POST";
                        $respiratory['request']['url']                  = "Observation";
                        $respiratory['resource']                        = $respiratoryresource;

                        $systolicresourcecodecoding['code']          = "8480-6";
                        $systolicresourcecodecoding['display']       = "Systolic blood pressure";
                        $systolicresourcecodecoding['system']        = "http://loinc.org";
                        $systolicresource['category'][]['coding'][]  = $categorycoding;
                        $systolicresource['code']['coding'][]        = $systolicresourcecodecoding;
                        $systolicresource['effectiveDateTime']       = $a->TRIAGE;
                        $systolicresource['encounter']               = $encounter;
                        $systolicresource['identifier'][]            = $identifier;
                        $systolicresource['issued']                  = $a->TRIAGE;
                        $systolicresource['performer'][]             = $performer;
                        $systolicresource['resourceType']            = "Observation";
                        $systolicresource['status']                  = "final";
                        $systolicresource['subject']                 = $subject;
                        $systolicresource['valueQuantity']['code']   = "mm[Hg]";
                        $systolicresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $systolicresource['valueQuantity']['unit']   = "mm[Hg]";
                        $systolicresource['valueQuantity']['value']  = floatval($a->TV_TEKANAN_DARAH);
                        $systolic['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $systolic['request']['method']               = "POST";
                        $systolic['request']['url']                  = "Observation";
                        $systolic['resource']                        = $systolicresource;

                        $diastolicresourcecodecoding['code']          = "8462-4";
                        $diastolicresourcecodecoding['display']       = "Diastolic blood pressure";
                        $diastolicresourcecodecoding['system']        = "http://loinc.org";
                        $diastolicresource['category'][]['coding'][]  = $categorycoding;
                        $diastolicresource['code']['coding'][]        = $diastolicresourcecodecoding;
                        $diastolicresource['effectiveDateTime']       = $a->TRIAGE;
                        $diastolicresource['encounter']               = $encounter;
                        $diastolicresource['identifier'][]            = $identifier;
                        $diastolicresource['issued']                  = $a->TRIAGE;
                        $diastolicresource['performer'][]             = $performer;
                        $diastolicresource['resourceType']            = "Observation";
                        $diastolicresource['status']                  = "final";
                        $diastolicresource['subject']                 = $subject;
                        $diastolicresource['valueQuantity']['code']   = "mm[Hg]";
                        $diastolicresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $diastolicresource['valueQuantity']['unit']   = "mm[Hg]";
                        $diastolicresource['valueQuantity']['value']  = floatval($a->TV_TEKANAN_DARAH2);
                        $diastolic['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $diastolic['request']['method']               = "POST";
                        $diastolic['request']['url']                  = "Observation";
                        $diastolic['resource']                        = $diastolicresource;

                        $tempresourcecodecoding['code']          = "8310-5";
                        $tempresourcecodecoding['display']       = "Body temperature";
                        $tempresourcecodecoding['system']        = "http://loinc.org";
                        $tempresource['category'][]['coding'][]  = $categorycoding;
                        $tempresource['code']['coding'][]        = $tempresourcecodecoding;
                        $tempresource['effectiveDateTime']       = $a->TRIAGE;
                        $tempresource['encounter']               = $encounter;
                        $tempresource['identifier'][]            = $identifier;
                        $tempresource['issued']                  = $a->TRIAGE;
                        $tempresource['performer'][]             = $performer;
                        $tempresource['resourceType']            = "Observation";
                        $tempresource['status']                  = "final";
                        $tempresource['subject']                 = $subject;
                        $tempresource['valueQuantity']['code']   = "Cel";
                        $tempresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $tempresource['valueQuantity']['unit']   = "C";
                        $tempresource['valueQuantity']['value']  = floatval($a->TV_SUHU);
                        $temp['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $temp['request']['method']               = "POST";
                        $temp['request']['url']                  = "Observation";
                        $temp['resource']                        = $tempresource;

                        $beratbadanresourcecodecoding['code']          = "29463-7";
                        $beratbadanresourcecodecoding['display']       = "Body weight";
                        $beratbadanresourcecodecoding['system']        = "http://loinc.org";
                        $beratbadanresource['category'][]['coding'][]  = $categorycoding;
                        $beratbadanresource['code']['coding'][]        = $beratbadanresourcecodecoding;
                        $beratbadanresource['effectiveDateTime']       = $a->TRIAGE;
                        $beratbadanresource['encounter']               = $encounter;
                        $beratbadanresource['identifier'][]            = $identifier;
                        $beratbadanresource['issued']                  = $a->TRIAGE;
                        $beratbadanresource['performer'][]             = $performer;
                        $beratbadanresource['resourceType']            = "Observation";
                        $beratbadanresource['status']                  = "final";
                        $beratbadanresource['subject']                 = $subject;
                        $beratbadanresource['valueQuantity']['code']   = "kg";
                        $beratbadanresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $beratbadanresource['valueQuantity']['unit']   = "kg";
                        $beratbadanresource['valueQuantity']['value']  = floatval($a->ANT_BB);
                        $beratbadan['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $beratbadan['request']['method']               = "POST";
                        $beratbadan['request']['url']                  = "Observation";
                        $beratbadan['resource']                        = $beratbadanresource;

                        $tinggibadanresourcecodecoding['code']          = "8302-2";
                        $tinggibadanresourcecodecoding['display']       = "Body height";
                        $tinggibadanresourcecodecoding['system']        = "http://loinc.org";
                        $tinggibadanresource['category'][]['coding'][]  = $categorycoding;
                        $tinggibadanresource['code']['coding'][]        = $tinggibadanresourcecodecoding;
                        $tinggibadanresource['effectiveDateTime']       = $a->TRIAGE;
                        $tinggibadanresource['encounter']               = $encounter;
                        $tinggibadanresource['identifier'][]            = $identifier;
                        $tinggibadanresource['issued']                  = $a->TRIAGE;
                        $tinggibadanresource['performer'][]             = $performer;
                        $tinggibadanresource['resourceType']            = "Observation";
                        $tinggibadanresource['status']                  = "final";
                        $tinggibadanresource['subject']                 = $subject;
                        $tinggibadanresource['valueQuantity']['code']   = "cm";
                        $tinggibadanresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $tinggibadanresource['valueQuantity']['unit']   = "cm";
                        $tinggibadanresource['valueQuantity']['value']  = floatval($a->ANT_TB);
                        $tinggibadan['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $tinggibadan['request']['method']               = "POST";
                        $tinggibadan['request']['url']                  = "Observation";
                        $tinggibadan['resource']                        = $tinggibadanresource;

                        $imtscaleresourcecodecoding['code']          = "39156-5";
                        $imtscaleresourcecodecoding['display']       = "Body mass index (BMI) [Ratio]";
                        $imtscaleresourcecodecoding['system']        = "http://loinc.org";
                        $imtscaleresource['category'][]['coding'][]  = $categorycoding;
                        $imtscaleresource['code']['coding'][]        = $imtscaleresourcecodecoding;
                        $imtscaleresource['effectiveDateTime']       = $a->TRIAGE;
                        $imtscaleresource['encounter']               = $encounter;
                        $imtscaleresource['identifier'][]            = $identifier;
                        $imtscaleresource['issued']                  = $a->TRIAGE;
                        $imtscaleresource['performer'][]             = $performer;
                        $imtscaleresource['resourceType']            = "Observation";
                        $imtscaleresource['status']                  = "final";
                        $imtscaleresource['subject']                 = $subject;
                        $imtscaleresource['valueQuantity']['code']   = "kg/m2";
                        $imtscaleresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $imtscaleresource['valueQuantity']['unit']   = "kg/m2";
                        $imtscaleresource['valueQuantity']['value']  = floatval($a->ANT_IMT);
                        $imtscale['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $imtscale['request']['method']               = "POST";
                        $imtscale['request']['url']                  = "Observation";
                        $imtscale['resource']                        = $imtscaleresource;
                        
                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        if(floatval($a->TV_FREK_NADI)!=0){$body['entry'][] = $heart;}
                        if(floatval($a->TV_FREK_NAFAS)!=0){$body['entry'][] = $respiratory;}
                        if(floatval($a->TV_TEKANAN_DARAH)!=0){$body['entry'][] = $systolic;}
                        if(floatval($a->TV_TEKANAN_DARAH2)!=0){$body['entry'][] = $diastolic;}
                        if(floatval($a->TV_SUHU)!=0){$body['entry'][] = $temp;}
                        if(floatval($a->ANT_BB)!=0){$body['entry'][] = $beratbadan;}
                        if(floatval($a->ANT_TB)!=0){$body['entry'][] = $tinggibadan;}
                        if(floatval($a->ANT_IMT)!=0){$body['entry'][] = $imtscale;}
                        
                        $response = Satusehat::postbundle(json_encode($body),$this->oauth['access_token']);

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
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                $simpanlog['JENIS']         = "1";

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
                                echo formatlogbundle($pasienid,$episodeid,'Observation','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'Observation','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'Observation','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetobservation = [];

                                            $responsegetobservation = Satusehat::getdata("Observation","identifier",$transid,$this->oauth['access_token']);

                                            if(isset($responsegetobservation['entry'])){
                                                foreach($responsegetobservation['entry'] as $responsegetobservations){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['IDENTIFIER']    = $transid;
                                                    $simpanlog['LOCATION']      = $responsegetobservations['fullUrl']."/_history/".trim($responsegetobservations['resource']['meta']['versionId'], 'W/"');
                                                    $simpanlog['RESOURCE_TYPE'] = $responsegetobservations['resource']['resourceType'];
                                                    $simpanlog['RESOURCE_ID']   = $responsegetobservations['resource']['id'];
                                                    $simpanlog['ETAG']          = 'W/"' . $responsegetobservations['resource']['meta']['versionId'] . '"';
                                                    $simpanlog['STATUS']        = "201 Created";
                                                    $simpanlog['LAST_MODIFIED'] = $responsegetobservations['resource']['meta']['lastUpdated'];
                                                    $simpanlog['ENVIRONMENT']   = SERVER;
                                                    $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                    $simpanlog['JENIS']         = "1";
                                                    
                                                    $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id']);

                                                    if(empty($resultcekdataresouce)){
                                                        $this->mss->insertdata($simpanlog);
                                                    }

                                                    $statusColor = "yellow";
                                                    $statusMsg   = "GET OBSERVATION BY TRANS ID";
                                                    echo formatlogbundle($pasienid,$episodeid,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id'], $statusMsg,$statusColor);
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

        public function anamnesaawaligd_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->AnamnesaawalIGD(SERVER);
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

                        $body           = [];
                        $encounter      = [];
                        $performer      = [];
                        $subject        = [];
                        $identifier     = [];
                        $categorycoding = [];

                        $suratrujukan                       = [];
                        $suratrujukanresource               = [];
                        $suratrujukanresourcecategorycoding = [];
                        $suratrujukanresourcecodecoding     = [];
                        $ctas                               = [];
                        $ctasresource                       = [];
                        $ctasresourcecodecoding             = [];
                        $ctasresourcectasresourcecoding     = [];
                        $ctasMapping                        = [];
                        $respiratory                        = [];
                        $respiratoryresource                = [];
                        $respiratoryresourcecodecoding      = [];
                        $systolic                           = [];
                        $systolicresource                   = [];
                        $systolicresourcecodecoding         = [];
                        $diastolic                          = [];
                        $diastolicresource                  = [];
                        $diastolicresourcecodecoding        = [];
                        $heart                              = [];
                        $heartresource                      = [];
                        $heartresourcecodecoding            = [];
                        $temp                               = [];
                        $tempresource                       = [];
                        $tempresourcecodecoding             = [];


                        $pasienid  = $a->PASIEN_ID;
                        $episodeid = $a->EPISODE_ID;
                        $poliid    = $a->POLI_ID;
                        $dokterid  = $a->DOKTER_ID;
                        $transid   = $a->TRANS_ID;

                        if(SERVER === "production"){
                            // $patientid           = $a->PATIENTID;
                            // $mrpas               = $a->PATIENTMR;
                            // $patientname         = $a->PATIENTNAME;
                            // $practitionerid      = $a->PRACTITIONERID;
                            // $practitionername    = $a->PRACTITIONERNAME;
                            // $locationid          = $a->LOCATIONID;
                            // $locationname        = $a->LOCATIONNAME;
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

                        $encounter['display']      = "Kunjungan Instalasi Gawat Darurat Medical Record ".$mrpas.' Atasnama '.$patientname;
                        $encounter['reference']    = "Encounter/".$a->RESOURCE_ID;
                        $performer['display']      = $practitionername;
                        $performer['reference']    = "Practitioner/".$practitionerid;
                        $subject['display']        = $patientname;
                        $subject['reference']      = "Patient/".$patientid;
                        $identifier['system']      = "http://sys-ids.kemkes.go.id/observation/".RS_ID;
                        $identifier['use']         = "official";
                        $identifier['value']       = $transid;
                        $categorycoding['code']    = "vital-signs";
                        $categorycoding['display'] = "Vital Signs";
                        $categorycoding['system']  = "http://terminology.hl7.org/CodeSystem/observation-category";
                        
                        $suratrujukanresourcecategorycoding['code']    = "survey";
                        $suratrujukanresourcecategorycoding['display'] = "Survey";
                        $suratrujukanresourcecategorycoding['system']  = "http://terminology.hl7.org/CodeSystem/observation-category";

                        $suratrujukanresourcecodecoding['code']    = "OC000034";
                        $suratrujukanresourcecodecoding['display'] = "Surat Pengantar Rujukan";
                        $suratrujukanresourcecodecoding['system']  = "http://terminology.kemkes.go.id/CodeSystem/clinical-term";

                        $suratrujukanresource['category'][]['coding'][] = $suratrujukanresourcecategorycoding;
                        $suratrujukanresource['code']['coding'][]       = $suratrujukanresourcecodecoding;
                        $suratrujukanresource['effectiveDateTime']      = $a->TRIAGE;
                        $suratrujukanresource['encounter']              = $encounter;
                        $suratrujukanresource['issued']                 = $a->TRIAGE;
                        $suratrujukanresource['performer'][]            = $performer;
                        $suratrujukanresource['resourceType']           = "Observation";
                        $suratrujukanresource['status']                 = "final";
                        $suratrujukanresource['subject']                = $subject;
                        $suratrujukanresource['identifier'][]           = $identifier;
                        $suratrujukanresource['valueBoolean']           = ($a->CARA_MASUK == 'UCRMASUK2');
                        $suratrujukan['fullUrl']                        = "urn:uuid:".Satusehat::uuid();
                        $suratrujukan['request']['method']              = "POST";
                        $suratrujukan['request']['url']                 = "Observation";
                        $suratrujukan['resource']                       = $suratrujukanresource;

                        $ctasresourcectasresourcecoding['code']    = "survey";
                        $ctasresourcectasresourcecoding['display'] = "Survey";
                        $ctasresourcectasresourcecoding['system']  = "http://terminology.hl7.org/CodeSystem/observation-category";

                        $ctasresourcecodecoding['code']    = "75910-0";
                        $ctasresourcecodecoding['display'] = "Canadian triage and acuity scale [CTAS]";
                        $ctasresourcecodecoding['system']  = "http://loinc.org";

                        $ctasMapping = ['UGWT1' => ['LA6113-0', '2'], 'UGWT2' => ['LA6114-8', '3'], 'UGWT3' => ['LA6115-5', '4'], 'UGWT4' => ['LA10137-0', '5']]; [$ctasresourcevalueCodeableConceptcoding['code'], $ctasresourcevalueCodeableConceptcoding['display']] = $ctasMapping[trim(strtoupper($a->TINGKAT_GAWAT))] ?? ['', '']; $ctasresourcevalueCodeableConceptcoding['system'] = "http://loinc.org";
                        $ctasresource['category'][]['coding'][]           = $ctasresourcectasresourcecoding;
                        $ctasresource['code']['coding'][]                 = $ctasresourcecodecoding;
                        $ctasresource['effectiveDateTime']                = $a->TRIAGE;
                        $ctasresource['encounter']                        = $encounter;
                        $ctasresource['issued']                           = $a->TRIAGE;
                        $ctasresource['performer'][]                      = $performer;
                        $ctasresource['resourceType']                     = "Observation";
                        $ctasresource['status']                           = "final";
                        $ctasresource['subject']                          = $subject;
                        $ctasresource['identifier'][]                     = $identifier;
                        $ctasresource['valueCodeableConcept']['coding'][] = $ctasresourcevalueCodeableConceptcoding;

                        $ctas['fullUrl']                        = "urn:uuid:".Satusehat::uuid();
                        $ctas['request']['method']              = "POST";
                        $ctas['request']['url']                 = "Observation";
                        $ctas['resource']                       = $ctasresource;

                        $respiratoryresourcecodecoding['code']          = "9279-1";
                        $respiratoryresourcecodecoding['display']       = "Respiratory rate";
                        $respiratoryresourcecodecoding['system']        = "http://loinc.org";
                        $respiratoryresource['category'][]['coding'][]  = $categorycoding;
                        $respiratoryresource['code']['coding'][]        = $respiratoryresourcecodecoding;
                        $respiratoryresource['effectiveDateTime']       = $a->TRIAGE;
                        $respiratoryresource['encounter']               = $encounter;
                        $respiratoryresource['identifier'][]            = $identifier;
                        $respiratoryresource['issued']                  = $a->TRIAGE;
                        $respiratoryresource['performer'][]             = $performer;
                        $respiratoryresource['resourceType']            = "Observation";
                        $respiratoryresource['status']                  = "final";
                        $respiratoryresource['subject']                 = $subject;
                        $respiratoryresource['valueQuantity']['code']   = "/min";
                        $respiratoryresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $respiratoryresource['valueQuantity']['unit']   = "beats/minute";
                        $respiratoryresource['valueQuantity']['value']  = floatval($a->BR_FREKUENSI_NAFAS);
                        $respiratory['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $respiratory['request']['method']               = "POST";
                        $respiratory['request']['url']                  = "Observation";
                        $respiratory['resource']                        = $respiratoryresource;

                        $systolicresourcecodecoding['code']          = "8480-6";
                        $systolicresourcecodecoding['display']       = "Systolic blood pressure";
                        $systolicresourcecodecoding['system']        = "http://loinc.org";
                        $systolicresource['category'][]['coding'][]  = $categorycoding;
                        $systolicresource['code']['coding'][]        = $systolicresourcecodecoding;
                        $systolicresource['effectiveDateTime']       = $a->TRIAGE;
                        $systolicresource['encounter']               = $encounter;
                        $systolicresource['identifier'][]            = $identifier;
                        $systolicresource['issued']                  = $a->TRIAGE;
                        $systolicresource['performer'][]             = $performer;
                        $systolicresource['resourceType']            = "Observation";
                        $systolicresource['status']                  = "final";
                        $systolicresource['subject']                 = $subject;
                        $systolicresource['valueQuantity']['code']   = "mm[Hg]";
                        $systolicresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $systolicresource['valueQuantity']['unit']   = "mm[Hg]";
                        $systolicresource['valueQuantity']['value']  = floatval($a->CIR_TKAN_DARAH);
                        $systolic['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $systolic['request']['method']               = "POST";
                        $systolic['request']['url']                  = "Observation";
                        $systolic['resource']                        = $systolicresource;

                        $diastolicresourcecodecoding['code']          = "8462-4";
                        $diastolicresourcecodecoding['display']       = "Diastolic blood pressure";
                        $diastolicresourcecodecoding['system']        = "http://loinc.org";
                        $diastolicresource['category'][]['coding'][]  = $categorycoding;
                        $diastolicresource['code']['coding'][]        = $diastolicresourcecodecoding;
                        $diastolicresource['effectiveDateTime']       = $a->TRIAGE;
                        $diastolicresource['encounter']               = $encounter;
                        $diastolicresource['identifier'][]            = $identifier;
                        $diastolicresource['issued']                  = $a->TRIAGE;
                        $diastolicresource['performer'][]             = $performer;
                        $diastolicresource['resourceType']            = "Observation";
                        $diastolicresource['status']                  = "final";
                        $diastolicresource['subject']                 = $subject;
                        $diastolicresource['valueQuantity']['code']   = "mm[Hg]";
                        $diastolicresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $diastolicresource['valueQuantity']['unit']   = "mm[Hg]";
                        $diastolicresource['valueQuantity']['value']  = floatval($a->CIR_TKAN_DARAH2);
                        $diastolic['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $diastolic['request']['method']               = "POST";
                        $diastolic['request']['url']                  = "Observation";
                        $diastolic['resource']                        = $diastolicresource;

                        $heartresourcecodecoding['code']          = "8867-4";
                        $heartresourcecodecoding['display']       = "Heart rate";
                        $heartresourcecodecoding['system']        = "http://loinc.org";
                        $heartresource['category'][]['coding'][]  = $categorycoding;
                        $heartresource['code']['coding'][]        = $heartresourcecodecoding;
                        $heartresource['effectiveDateTime']       = $a->TRIAGE;
                        $heartresource['encounter']               = $encounter;
                        $heartresource['identifier'][]            = $identifier;
                        $heartresource['issued']                  = $a->TRIAGE;
                        $heartresource['performer'][]             = $performer;
                        $heartresource['resourceType']            = "Observation";
                        $heartresource['status']                  = "final";
                        $heartresource['subject']                 = $subject;
                        $heartresource['valueQuantity']['code']   = "/min";
                        $heartresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $heartresource['valueQuantity']['unit']   = "breaths/minute";
                        $heartresource['valueQuantity']['value']  = floatval($a->CIR_NADI_TERABA);
                        $heart['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $heart['request']['method']               = "POST";
                        $heart['request']['url']                  = "Observation";
                        $heart['resource']                        = $heartresource;

                        $tempresourcecodecoding['code']          = "8310-5";
                        $tempresourcecodecoding['display']       = "Body temperature";
                        $tempresourcecodecoding['system']        = "http://loinc.org";
                        $tempresource['category'][]['coding'][]  = $categorycoding;
                        $tempresource['code']['coding'][]        = $tempresourcecodecoding;
                        $tempresource['effectiveDateTime']       = $a->TRIAGE;
                        $tempresource['encounter']               = $encounter;
                        $tempresource['identifier'][]            = $identifier;
                        $tempresource['issued']                  = $a->TRIAGE;
                        $tempresource['performer'][]             = $performer;
                        $tempresource['resourceType']            = "Observation";
                        $tempresource['status']                  = "final";
                        $tempresource['subject']                 = $subject;
                        $tempresource['valueQuantity']['code']   = "Cel";
                        $tempresource['valueQuantity']['system'] = "http://unitsofmeasure.org";
                        $tempresource['valueQuantity']['unit']   = "C";
                        $tempresource['valueQuantity']['value'] = floatval(str_replace(',', '.', $a->SUHU_AXILA)) >= 100 ? floatval($a->SUHU_AXILA) / 10 : floatval(str_replace(',', '.', $a->SUHU_AXILA));
                        $temp['fullUrl']                         = "urn:uuid:".Satusehat::uuid();
                        $temp['request']['method']               = "POST";
                        $temp['request']['url']                  = "Observation";
                        $temp['resource']                        = $tempresource;

                        
                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $suratrujukan;
                        $body['entry'][]      = $ctas;
                        if(floatval($a->CIR_NADI_TERABA)!=0){$body['entry'][] = $heart;}
                        if(floatval($a->BR_FREKUENSI_NAFAS)!=0){$body['entry'][] = $respiratory;}
                        if(floatval($a->CIR_TKAN_DARAH)!=0){$body['entry'][] = $systolic;}
                        if(floatval($a->CIR_TKAN_DARAH2)!=0){$body['entry'][] = $diastolic;}
                        if(floatval($a->SUHU_AXILA)!=0){$body['entry'][] = $temp;}
                        
                        $response = Satusehat::postbundle(json_encode($body),$this->oauth['access_token']);

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
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                $simpanlog['JENIS']         = "3";

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
                                echo formatlogbundle($pasienid,$episodeid,'Observation','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'Observation','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'Observation','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetobservation = [];

                                            $responsegetobservation = Satusehat::getdata("Observation","identifier",$transid,$this->oauth['access_token']);

                                            if(isset($responsegetobservation['entry'])){
                                                foreach($responsegetobservation['entry'] as $responsegetobservations){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['IDENTIFIER']    = $transid;
                                                    $simpanlog['LOCATION']      = $responsegetobservations['fullUrl']."/_history/".trim($responsegetobservations['resource']['meta']['versionId'], 'W/"');
                                                    $simpanlog['RESOURCE_TYPE'] = $responsegetobservations['resource']['resourceType'];
                                                    $simpanlog['RESOURCE_ID']   = $responsegetobservations['resource']['id'];
                                                    $simpanlog['ETAG']          = 'W/"' . $responsegetobservations['resource']['meta']['versionId'] . '"';
                                                    $simpanlog['STATUS']        = "201 Created";
                                                    $simpanlog['LAST_MODIFIED'] = $responsegetobservations['resource']['meta']['lastUpdated'];
                                                    $simpanlog['ENVIRONMENT']   = SERVER;
                                                    $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                    $simpanlog['JENIS']         = "3";
                                                    
                                                    $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id']);

                                                    if(empty($resultcekdataresouce)){
                                                        $this->mss->insertdata($simpanlog);
                                                    }

                                                    $statusColor = "yellow";
                                                    $statusMsg   = "GET OBSERVATION BY TRANS ID";
                                                    echo formatlogbundle($pasienid,$episodeid,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id'], $statusMsg,$statusColor);
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

                        $body       = [];
                        $encounter  = [];
                        $performer  = [];
                        $subject    = [];
                        $identifier = [];

                        $psiokologis                                   = [];
                        $psiokologisresource                           = [];
                        $psiokologisresourcecategorycoding             = [];
                        $psiokologisresourcecodecoding                 = [];
                        $psiokologisresourcevalueCodeableConceptcoding = [];
                        $psikologiMapping                              = [];

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

                        $encounter['display']      = "Kunjungan Instalasi Gawat Darurat Medical Record ".$mrpas.' Atasnama '.$patientname;
                        $encounter['reference']    = "Encounter/".$a->RESOURCE_ID;
                        $performer['display']      = $practitionername;
                        $performer['reference']    = "Practitioner/".$practitionerid;
                        $subject['display']        = $patientname;
                        $subject['reference']      = "Patient/".$patientid;
                        $identifier['system']      = "http://sys-ids.kemkes.go.id/observation/".RS_ID;
                        $identifier['use']         = "official";
                        $identifier['value']       = $transid;

                        $psiokologisresourcecategorycoding['code']    = "survey";
                        $psiokologisresourcecategorycoding['display'] = "Survey";
                        $psiokologisresourcecategorycoding['system']  = "http://terminology.hl7.org/CodeSystem/observation-category";

                        $psiokologisresourcecodecoding['code']    = "8693-4";
                        $psiokologisresourcecodecoding['display'] = "Mental Status";
                        $psiokologisresourcecodecoding['system']  = "http://loinc.org";

                        $psikologiMapping = ['0' => ['386661006', 'Angry'], '1' => ['48694002', 'Feeling anxious'], '2' => ['48694002', 'Feeling anxious'], '3' => ['35489007', 'Depressive disorder'], '4' => ['48694002', 'Feeling anxious'], '5' => [' suicidal', 'Suicidal tendency'], '6' => ['17326005', 'Normal mental state']]; [$psiokologisresourcevalueCodeableConceptcoding['code'], $psiokologisresourcevalueCodeableConceptcoding['display']] = $psikologiMapping[(string) $a->PSIKOLOGI] ?? ['', '']; $psiokologisresourcevalueCodeableConceptcoding['system'] = "http://snomed.info/sct";

                        $psiokologisresource['category'][]['coding'][] = $psiokologisresourcecategorycoding;
                        $psiokologisresource['code']['coding'][]       = $psiokologisresourcecodecoding;
                        $psiokologisresource['effectiveDateTime']      = $a->TRIAGE;
                        $psiokologisresource['encounter']              = $encounter;
                        $psiokologisresource['issued']                 = $a->TRIAGE;
                        $psiokologisresource['performer'][]            = $performer;
                        $psiokologisresource['resourceType']           = "Observation";
                        $psiokologisresource['status']                 = "final";
                        $psiokologisresource['subject']                = $subject;
                        $psiokologisresource['identifier'][]                     = $identifier;
                        $psiokologisresource['valueCodeableConcept']['coding'][] = $psiokologisresourcevalueCodeableConceptcoding;

                        $psiokologis['fullUrl']                        = "urn:uuid:".Satusehat::uuid();
                        $psiokologis['request']['method']              = "POST";
                        $psiokologis['request']['url']                 = "Observation";
                        $psiokologis['resource']                       = $psiokologisresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $psiokologis;
                        
                        $response = Satusehat::postbundle(json_encode($body),$this->oauth['access_token']);

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
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                $simpanlog['JENIS']         = "4";

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
                                echo formatlogbundle($pasienid,$episodeid,'Observation','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'Observation','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'Observation','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetobservation = [];

                                            $responsegetobservation = Satusehat::getdata("Observation","identifier",$transid,$this->oauth['access_token']);

                                            if(isset($responsegetobservation['entry'])){
                                                foreach($responsegetobservation['entry'] as $responsegetobservations){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['IDENTIFIER']    = $transid;
                                                    $simpanlog['LOCATION']      = $responsegetobservations['fullUrl']."/_history/".trim($responsegetobservations['resource']['meta']['versionId'], 'W/"');
                                                    $simpanlog['RESOURCE_TYPE'] = $responsegetobservations['resource']['resourceType'];
                                                    $simpanlog['RESOURCE_ID']   = $responsegetobservations['resource']['id'];
                                                    $simpanlog['ETAG']          = 'W/"' . $responsegetobservations['resource']['meta']['versionId'] . '"';
                                                    $simpanlog['STATUS']        = "201 Created";
                                                    $simpanlog['LAST_MODIFIED'] = $responsegetobservations['resource']['meta']['lastUpdated'];
                                                    $simpanlog['ENVIRONMENT']   = SERVER;
                                                    $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                    $simpanlog['JENIS']         = "4";
                                                    
                                                    $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id']);

                                                    if(empty($resultcekdataresouce)){
                                                        $this->mss->insertdata($simpanlog);
                                                    }

                                                    $statusColor = "yellow";
                                                    $statusMsg   = "GET OBSERVATION BY TRANS ID";
                                                    echo formatlogbundle($pasienid,$episodeid,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id'], $statusMsg,$statusColor);
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

        public function hasillab_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->hasillab(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor             = "";
                        $statusMsg               = "";
                        $patientid               = "";
                        $mrpas                   = "";
                        $patientname             = "";
                        $pasienid                = "";
                        $episodeid               = "";
                        $poliid                  = "";
                        $dokterid                = "";
                        $sampelid                = "";
                        $identifier              = "";
                        $layanid                 = "";
                        $uuidobservationhasillab = "";

                        $body                                 = [];
                        $observationlab                       = [];
                        $observationlabresource               = [];
                        $observationlabresourceidentifier     = [];
                        $observationlabresourcebasedOn        = [];
                        $observationlabresourcecategory       = [];
                        $observationlabresourcecategorycoding = [];
                        $observationlabresourcecode           = [];
                        $observationlabresourceencounter      = [];
                        $observationlabresourceinterpretation = [];
                        $observationlabresourceperformer      = [];
                        
                        $pasienid   = $a->PASIEN_ID;
                        $episodeid  = $a->EPISODE_ID;
                        $poliid     = $a->POLI_ID;
                        $dokterid   = $a->DOKTER_ID;
                        $layanid    = $a->TES_ID;
                        $sampelid   = $a->SAMPEL_ID;
                        $identifier = $a->SAMPEL_ID."-".$a->TES_ID;

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

                        $uuidobservationhasillab = Satusehat::uuid();

                        $observationlabresourcecategorycoding['system']  = "http://terminology.hl7.org/CodeSystem/observation-category";
                        $observationlabresourcecategorycoding['code']    = "laboratory";
                        $observationlabresourcecategorycoding['display'] = "Laboratory";
                        
                        $observationlabresourceidentifier['system'] = "http://sys-ids.kemkes.go.id/observation/".RS_ID;
                        $observationlabresourceidentifier['use']    = "official";
                        $observationlabresourceidentifier['value']  = $identifier;
                        $observationlabresourcebasedOn['reference'] = "ServiceRequest/".$a->SERVICERQUESTID;
                        $observationlabresourcecategory['coding'][] = $observationlabresourcecategorycoding;
                        
                        if($a->TES_ID==="NA"){
                            $observationlabresourcecode['system']         = "http://loinc.org";
                            $observationlabresourcecode['code']           = "2947-0";
                            $observationlabresourcecode['display']        = "Sodium [Moles/volume] in Blood";
                        }

                        if($a->TES_ID==="CL"){
                            $observationlabresourcecode['system']         = "http://loinc.org";
                            $observationlabresourcecode['code']           = "2069-3";
                            $observationlabresourcecode['display']        = "Chloride [Moles/volume] in Blood";
                        }

                        if($a->TES_ID==="K"){
                            $observationlabresourcecode['system']         = "http://loinc.org";
                            $observationlabresourcecode['code']           = "6298-4";
                            $observationlabresourcecode['display']        = "Potassium [Moles/volume] in Blood";
                        }
                        $observationlabresourceencounter['reference'] = "Encounter/".$a->RESOURCEID;
                        $observationlabresourceperformer['reference'] = "Organization/".RS_ID;

                        $observationlabresource['resourceType']              = "Observation";
                        $observationlabresource['identifier'][]              = $observationlabresourceidentifier;
                        $observationlabresource['basedOn'][]                 = $observationlabresourcebasedOn;
                        $observationlabresource['category'][]                = $observationlabresourcecategory;
                        $observationlabresource['code']['coding'][]          = $observationlabresourcecode;
                        $observationlabresource['effectiveDateTime']         = $a->TGLHASIL;
                        $observationlabresource['encounter']                 = $observationlabresourceencounter;
                        $observationlabresource['issued']                    = $a->TGLHASIL;
                        $observationlabresource['performer'][]               = $observationlabresourceperformer;
                        $observationlabresource['specimen']['reference']     = "Specimen/".$a->SPECIMENID;
                        $observationlabresource['status']                    = "final";
                        $observationlabresource['subject']['reference']      = "Patient/".$patientid;
                        $observationlabresource['subject']['display']        = $patientname;
                        $observationlabresource['valueQuantity']['value']    = (float) $a->HASIL_NILAI;
                        $observationlabresource['valueQuantity']['unit']     = "mmol/L";
                        $observationlabresource['valueQuantity']['system']   = "http://unitsofmeasure.org";
                        $observationlabresource['valueQuantity']['code']     = "mmol/L";

                        $observationlab['fullUrl']           = "urn:uuid:".$uuidobservationhasillab;
                        $observationlab['request']['method'] = "POST";
                        $observationlab['request']['url']    = "Observation";
                        $observationlab['resource']          = $observationlabresource;

                        $body['resourceType'] = "Bundle";
                        $body['type']         = "transaction";
                        $body['entry'][]      = $observationlab;

                        // $this->response($observationlabresource);

                        $response = Satusehat::postbundle(json_encode($body),$this->oauth['access_token']);

                        if(isset($response['entry'])){
                            foreach($response['entry'] as $entrys){
                                $simpanlog = [];

                                $simpanlog['PASIEN_ID']     = $pasienid;
                                $simpanlog['EPISODE_ID']    = $episodeid;
                                $simpanlog['POLI_ID']       = $poliid;
                                $simpanlog['DOKTER_ID']     = $dokterid;
                                $simpanlog['IDENTIFIER']    = $identifier;
                                $simpanlog['SAMPEL_ID']     = $sampelid;
                                $simpanlog['LAYAN_ID']      = $layanid;
                                $simpanlog['LOCATION']      = $entrys['response']['location'];
                                $simpanlog['RESOURCE_TYPE'] = $entrys['response']['resourceType'];
                                $simpanlog['RESOURCE_ID']   = $entrys['response']['resourceID'];
                                $simpanlog['ETAG']          = $entrys['response']['etag'];
                                $simpanlog['STATUS']        = $entrys['response']['status'];
                                $simpanlog['LAST_MODIFIED'] = $entrys['response']['lastModified'];
                                $simpanlog['ENVIRONMENT']   = SERVER;
                                $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                $simpanlog['JENIS']         = "2";

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
                                echo formatlogbundle($pasienid,$episodeid,'Observation','','ERROR | response | NULL response from SATUSEHAT',$statusColor);
                            }else{
                                if(isset($response['fault'])){
                                    $faultString = $response['fault']['faultstring'] ?? 'Unknown fault';
                                    $errorCode   = $response['fault']['detail']['errorcode'] ?? 'unknown';
                                    $statusColor = 'red';
                                    $statusMsg   = 'FAULT | ' . $errorCode . ' | ' . $faultString;

                                    echo formatlogbundle($pasienid,$episodeid,'Observation','',$statusMsg,$statusColor);
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
                                                echo formatlogbundle($pasienid,$episodeid,'Observation','',$statusMsg,$statusColor);
                                            }
                                            
                                        }
                                    }

                                    if(isset($response['issue'])){
                                        if($response['issue'][0]['code']==="duplicate"){
                                            $responsegetobservation = [];

                                            $responsegetobservation = Satusehat::getdata("Observation","identifier",$transid,$this->oauth['access_token']);

                                            if(isset($responsegetobservation['entry'])){
                                                foreach($responsegetobservation['entry'] as $responsegetobservations){
                                                    $simpanlog=[];

                                                    $simpanlog['PASIEN_ID']     = $pasienid;
                                                    $simpanlog['EPISODE_ID']    = $episodeid;
                                                    $simpanlog['POLI_ID']       = $poliid;
                                                    $simpanlog['DOKTER_ID']     = $dokterid;
                                                    $simpanlog['IDENTIFIER']    = $identifier;
                                                    $simpanlog['SAMPEL_ID']     = $sampelid;
                                                    $simpanlog['LAYAN_ID']      = $layanid;
                                                    $simpanlog['LOCATION']      = $responsegetobservations['fullUrl']."/_history/".trim($responsegetobservations['resource']['meta']['versionId'], 'W/"');
                                                    $simpanlog['RESOURCE_TYPE'] = $responsegetobservations['resource']['resourceType'];
                                                    $simpanlog['RESOURCE_ID']   = $responsegetobservations['resource']['id'];
                                                    $simpanlog['ETAG']          = 'W/"' . $responsegetobservations['resource']['meta']['versionId'] . '"';
                                                    $simpanlog['STATUS']        = "201 Created";
                                                    $simpanlog['LAST_MODIFIED'] = $responsegetobservations['resource']['meta']['lastUpdated'];
                                                    $simpanlog['ENVIRONMENT']   = SERVER;
                                                    $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                    $simpanlog['JENIS']         = "2";
                                                    
                                                    $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id']);

                                                    if(empty($resultcekdataresouce)){
                                                        $this->mss->insertdata($simpanlog);
                                                    }

                                                    $statusColor = "yellow";
                                                    $statusMsg   = "GET OBSERVATION BY TRANS ID";
                                                    echo formatlogbundle($pasienid,$episodeid,$responsegetobservations['resource']['resourceType'],$responsegetobservations['resource']['id'], $statusMsg,$statusColor);
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