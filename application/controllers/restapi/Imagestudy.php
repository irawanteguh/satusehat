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

    class Imagestudy extends REST_Controller{
        public static $oauth;
        private $dicomPath = 'E:/xampp/htdocs/rsudpasarminggu/prod/satusehat/assets/dicom';

        public function __construct(){
            parent::__construct();
            $this->load->model("Modeilimagesstudy", "md");
            $this->load->model("Modelsatusehat", "mss");

            $reqbody     = $this->input->raw_input_stream;
            $reqbodyjson = json_decode($reqbody, true);

            Satusehat::init();
            self::$oauth  = Satusehat::generatedoauth();
            headerbundle();
        }
        
        public function dicomx_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->dicom(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor = "";
                        $statusMsg   = "";
                        $pasienid    = "";
                        $episodeid   = "";
                        $poliid      = "";
                        $transco     = "";
                        $acsn        = "";
                        $layanid     = "";
                        $dokterid    = "";
                        $identifier  = "";
                        $satusehatid = "";

                        $pasienid   = $a->PASIEN_ID;
                        $episodeid  = $a->EPISODE_ID;
                        $poliid     = $a->POLI_ID;
                        $transco    = $a->TRANS_CO;
                        $acsn       = $a->ACSN;
                        $layanid    = $a->LAYAN_ID;
                        $dokterid   = $a->DOKTER_ID;
                        $identifier = $a->TRANS_CO."-".$a->LAYAN_ID;

                        $folders = glob($this->dicomPath . '/*');
                        $folders = array_filter($folders, 'is_file');

                        if(!empty($folders)){
                            $foundMatch = false;
                            foreach ($folders as $file) {
                                $dcmdump     = 'C:\dcmtk\bin\dcmdump.exe';
                                
                                if(file_exists($dcmdump)){
                                    if(file_exists($file)){
                                        $cmd    = '"'.$dcmdump.'" '.escapeshellarg($file).' 2>&1';
                                        $output = shell_exec($cmd);

                                        if($output){
                                            if(preg_match('/\(0008,0050\).*?\[(.*?)\]/', $output, $m)){
                                                $acsnDicom = trim($m[1]);
                                                $acsnDb    = trim($acsn);

                                                if($acsnDicom == $acsnDb){
                                                    $foundMatch = true;
                                                    $dcmdjpeg   = 'C:\dcmtk\bin\dcmdjpeg.exe';
                                                    $storescu   = 'C:\dcmtk\bin\storescu.exe';

                                                    $remoteIp   = '10.12.120.58';
                                                    $remotePort = '11112';
                                                    $remoteAE   = 'DCMROUTER';
                                                    $localAE    = 'RSUDPMS';

                                                    if(file_exists($storescu) || file_exists($dcmdjpeg)){
                                                        if(file_exists($file)){
                                                            $tmpFile       = sys_get_temp_dir().'\\dcmd_'.uniqid().'.dcm';
                                                            $cmdDecompress = '"'.$dcmdjpeg.'" '.escapeshellarg($file).' '.escapeshellarg($tmpFile).' 2>&1';
                                                            $outDecompress = shell_exec($cmdDecompress);

                                                            if(file_exists($tmpFile)){
                                                                $cmdSend = '"'.$storescu.'" -v '.'-aec '.escapeshellarg($remoteAE).' '.'-aet '.escapeshellarg($localAE).' '.$remoteIp.' '.$remotePort.' '.escapeshellarg($tmpFile).' 2>&1';
                                                                $outSend = shell_exec($cmdSend);
                                                                @unlink($tmpFile);

                                                                $responsegetImageStudy = [];
                                                                $parameter                 = "http://sys-ids.kemkes.go.id/acsn/".RS_ID."|".$acsn;
                                                                $responsegetImageStudy = Satusehat::getdata("ImagingStudy","identifier",$parameter,self::$oauth['access_token']);

                                                                if(isset($responsegetImageStudy['entry'])){
                                                                    foreach($responsegetImageStudy['entry'] as $responsegetImageStudys){
                                                                        $simpanlog=[];

                                                                        $simpanlog['PASIEN_ID']     = $pasienid;
                                                                        $simpanlog['EPISODE_ID']    = $episodeid;
                                                                        $simpanlog['POLI_ID']       = $poliid;
                                                                        $simpanlog['DOKTER_ID']     = $dokterid;
                                                                        $simpanlog['TRANS_CO']      = $transco;
                                                                        $simpanlog['LAYAN_ID']      = $layanid;
                                                                        $simpanlog['ACSN']          = $acsn;
                                                                        $simpanlog['IDENTIFIER']    = $identifier;
                                                                        $simpanlog['LOCATION']      = $responsegetImageStudys['fullUrl']."/_history/".trim($responsegetImageStudys['resource']['meta']['versionId'], 'W/"');
                                                                        $simpanlog['RESOURCE_TYPE'] = $responsegetImageStudys['resource']['resourceType'];
                                                                        $simpanlog['RESOURCE_ID']   = $responsegetImageStudys['resource']['id'];
                                                                        $simpanlog['ETAG']          = 'W/"' . $responsegetImageStudys['resource']['meta']['versionId'] . '"';
                                                                        $simpanlog['STATUS']        = "201 Created";
                                                                        $simpanlog['LAST_MODIFIED'] = $responsegetImageStudys['resource']['meta']['lastUpdated'];
                                                                        $simpanlog['ENVIRONMENT']   = SERVER;
                                                                        $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                                        $simpanlog['JENIS']         = "1";

                                                                        $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetImageStudys['resource']['resourceType'],$responsegetImageStudys['resource']['id']);
                                                                        if(empty($resultcekdataresouce)){
                                                                            // $this->mss->insertdata($simpanlog);
                                                                        }

                                                                        $statusColor = 'green';
                                                                        $statusMsg   = "OK";
                                                                        $satusehatid = $responsegetImageStudys['resource']['id'];
                                                                        break;
                                                                    }
                                                                }
                                                            }else{
                                                                $statusColor = 'red';
                                                                $statusMsg   = "Gagal decompress";
                                                            }
                                                        }else{
                                                            $statusColor = 'red';
                                                            $statusMsg   = "File DICOM tidak ditemukan";
                                                        }
                                                    }else{
                                                        $statusColor = 'red';
                                                        $statusMsg   = "dcmtk tools tidak ditemukan";
                                                    }
                                                }else{
                                                    $statusColor = 'red';
                                                    $statusMsg   = "File DICOM tidak ditemukan ACSN ".$acsnDb." Tidak Ada Yang Cocok";
                                                }
                                            }
                                        }else{
                                            $statusColor = "red";
                                            $statusMsg   = "Tidak ada output dari dcmdum";
                                        }
                                    }else{
                                        $statusColor = "red";
                                        $statusMsg   = "File DICOM tidak ditemukan";
                                    }
                                }else{
                                    $statusColor = "red";
                                    $statusMsg   = "dcmdump.exe tidak ditemukan";
                                }
                            }
                            
                        }else{
                            $statusColor = "red";
                            $statusMsg   = "Tidak Ditemukan File Dicom Pada Folder";
                        }

                        echo formatlogbundle($pasienid,$episodeid,'ImageStudy',$satusehatid,$statusMsg,$statusColor);
                    }
                }else{
                    echo color('red')."Data Tidak Ditemukan";
                }
            }else{
                echo color('red').self::$oauth['issue'][0]['details']['text'];
            }
        }

        public function dicom_post(){
            if(!isset(self::$oauth['issue'])){
                $result = $this->md->dicom(SERVER);
                if(!empty($result)){
                    foreach ($result as $a){
                        $statusColor = "";
                        $statusMsg   = "";
                        $satusehatid = "";
                        $pasienid    = "";
                        $episodeid   = "";
                        $poliid      = "";
                        $transco     = "";
                        $acsn        = "";
                        $layanid     = "";
                        $dokterid    = "";
                        $identifier  = "";

                        $pasienid   = $a->PASIEN_ID;
                        $episodeid  = $a->EPISODE_ID;
                        $poliid     = $a->POLI_ID;
                        $transco    = $a->TRANS_CO;
                        $acsn       = $a->ACSN;
                        $layanid    = $a->LAYAN_ID;
                        $dokterid   = $a->DOKTER_ID;
                        $identifier = $a->TRANS_CO."-".$a->LAYAN_ID;

                        $folders = glob($this->dicomPath . '/*');
                        $folders = array_filter($folders, 'is_file');

                        if(!empty($folders)){
                            foreach($folders as $file){
                                $colors     = "";
                                $infos      = "";
                                $foundMatch = false;
                                $dcmdump    = 'C:\dcmtk\bin\dcmdump.exe';

                                if(file_exists($dcmdump)){
                                    if(file_exists($file)){
                                        $cmd    = '"'.$dcmdump.'" '.escapeshellarg($file).' 2>&1';
                                        $output = shell_exec($cmd);
                                        if($output){
                                            if(preg_match('/\(0008,0050\).*?\[(.*?)\]/', $output, $m)){
                                                $acsnDicom = trim($m[1]);
                                                $acsnDb    = trim($acsn);

                                                if($acsnDicom === $acsnDb){
                                                    $dcmdjpeg   = 'C:\dcmtk\bin\dcmdjpeg.exe';
                                                    $storescu   = 'C:\dcmtk\bin\storescu.exe';

                                                    $remoteIp   = '10.12.120.58';
                                                    $remotePort = '11112';
                                                    $remoteAE   = 'DCMROUTER';
                                                    $localAE    = 'RSUDPMS';

                                                    if(file_exists($storescu) || file_exists($dcmdjpeg)){
                                                        if(file_exists($file)){
                                                            $tmpFile       = sys_get_temp_dir().'\\dcmd_'.uniqid().'.dcm';
                                                            $cmdDecompress = '"'.$dcmdjpeg.'" '.escapeshellarg($file).' '.escapeshellarg($tmpFile).' 2>&1';
                                                            $outDecompress = shell_exec($cmdDecompress);

                                                            if(file_exists($tmpFile)){
                                                                $cmdSend = '"'.$storescu.'" -v '.'-aec '.escapeshellarg($remoteAE).' '.'-aet '.escapeshellarg($localAE).' '.$remoteIp.' '.$remotePort.' '.escapeshellarg($tmpFile).' 2>&1';
                                                                $outSend = shell_exec($cmdSend);
                                                                @unlink($tmpFile);

                                                                $responsegetImageStudy = [];
                                                                $parameter             = "http://sys-ids.kemkes.go.id/acsn/".RS_ID."|".$acsn;
                                                                $responsegetImageStudy = Satusehat::getdata("ImagingStudy","identifier",$parameter,self::$oauth['access_token']);

                                                                if(isset($responsegetImageStudy['entry'])){
                                                                    foreach($responsegetImageStudy['entry'] as $responsegetImageStudys){
                                                                        $simpanlog=[];

                                                                        $simpanlog['PASIEN_ID']     = $pasienid;
                                                                        $simpanlog['EPISODE_ID']    = $episodeid;
                                                                        $simpanlog['POLI_ID']       = $poliid;
                                                                        $simpanlog['DOKTER_ID']     = $dokterid;
                                                                        $simpanlog['TRANS_CO']      = $transco;
                                                                        $simpanlog['LAYAN_ID']      = $layanid;
                                                                        $simpanlog['ACSN']          = $acsn;
                                                                        $simpanlog['IDENTIFIER']    = $identifier;
                                                                        $simpanlog['LOCATION']      = $responsegetImageStudys['fullUrl']."/_history/".trim($responsegetImageStudys['resource']['meta']['versionId'], 'W/"');
                                                                        $simpanlog['RESOURCE_TYPE'] = $responsegetImageStudys['resource']['resourceType'];
                                                                        $simpanlog['RESOURCE_ID']   = $responsegetImageStudys['resource']['id'];
                                                                        $simpanlog['ETAG']          = 'W/"' . $responsegetImageStudys['resource']['meta']['versionId'] . '"';
                                                                        $simpanlog['STATUS']        = "201 Created";
                                                                        $simpanlog['LAST_MODIFIED'] = $responsegetImageStudys['resource']['meta']['lastUpdated'];
                                                                        $simpanlog['ENVIRONMENT']   = SERVER;
                                                                        $simpanlog['CREATED_BY']    = "MIDDLEWARE";
                                                                        $simpanlog['JENIS']         = "1";

                                                                        $resultcekdataresouce = $this->mss->cekdataresouce(SERVER,$responsegetImageStudys['resource']['resourceType'],$responsegetImageStudys['resource']['id']);
                                                                        if(empty($resultcekdataresouce)){
                                                                            $this->mss->insertdata($simpanlog);
                                                                        }

                                                                        $colors      = 'green';
                                                                        $infos       = "OK";
                                                                        $foundMatch  = true;
                                                                        $satusehatid = $responsegetImageStudys['resource']['id'];
                                                                        break;
                                                                    }
                                                                }
                                                            }else{
                                                                $colors     = 'red';
                                                                $infos      = "Gagal decompress";
                                                                $foundMatch = false;
                                                            }
                                                        }else{
                                                            $colors     = 'red';
                                                            $infos      = "File DICOM tidak ditemukan";
                                                            $foundMatch = false;
                                                        }
                                                    }else{
                                                        $colors     = 'red';
                                                        $infos      = "dcmtk tools tidak ditemukan";
                                                        $foundMatch = false;
                                                    }

                                                    $colors      = $colors;
                                                    $infos       = $infos;
                                                    $foundMatch  = true;
                                                    $satusehatid = $satusehatid;
                                                    break;
                                                }else{
                                                    $colors     = 'red';
                                                    $infos      = "File DICOM tidak ditemukan ACSN ".$acsnDb." Tidak Ada Yang Cocok";
                                                    $foundMatch = false;
                                                }
                                            }
                                        }else{
                                            $colors     = 'red';
                                            $infos      = "Tidak ada output dari dcmdum";
                                            $foundMatch = false;
                                        }
                                    }else{
                                        $colors     = 'red';
                                        $infos      = "File DICOM tidak ditemukan";
                                        $foundMatch = false;
                                    }
                                }else{
                                    $colors     = 'red';
                                    $infos      = "dcmdump.exe tidak ditemukan";
                                    $foundMatch = false;
                                }
                            }

                            $statusColor = $colors;
                            $statusMsg   = $infos;
                        }else{
                            $statusColor = "red";
                            $statusMsg   = "Tidak Ditemukan File Dicom Pada Folder";
                        }

                        echo formatlogbundle($pasienid,$episodeid,'ImageStudy',$satusehatid,$statusMsg,$statusColor);
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