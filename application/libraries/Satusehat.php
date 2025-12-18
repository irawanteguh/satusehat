<?php
    class Satusehat{
        public static $rsid;
        public static $clientid;
        public static $clientsecret;
        public static $baseurloauth;
        public static $baseurl;
        public static $baseurlbundle;
        public static $baseurlkfa;
        public static $baseurlconcent;
        public static $oauth;

        public static function init(){
            self::$rsid           = RS_ID;
            self::$clientid       = CLIENT_ID;
            self::$clientsecret   = CLIENT_SECRET;
            self::$baseurloauth   = OAUTH_URL;
            self::$baseurl        = BASE_URL;
            self::$baseurlbundle  = BASE_URL_BUNDLE;
            self::$baseurlkfa     = BASE_URL_KFA;
            self::$baseurlconcent = CONCENT_URL;
        }

        public static function getRandomPatient() {
            $patients = [
                ["nik" => "9271060312000001", "nama" => "Ardianto Putra", "gender" => "male", "birthDate" => "1992-01-09", "ihs" => "P02478375538"],
                ["nik" => "9204014804000002", "nama" => "Claudia Sintia", "gender" => "female", "birthDate" => "1989-11-03", "ihs" => "P03647103112"],
                ["nik" => "9104224509000003", "nama" => "Elizabeth Dior", "gender" => "female", "birthDate" => "1993-09-05", "ihs" => "P00805884304"],
                ["nik" => "9104223107000004", "nama" => "Dr. Alan Bagus Prasetya", "gender" => "male", "birthDate" => "1977-09-03", "ihs" => "P00912894463"],
                ["nik" => "9104224606000005", "nama" => "Ghina Assyifa", "gender" => "female", "birthDate" => "2004-08-21", "ihs" => "P01654557057"],
                ["nik" => "9104025209000006", "nama" => "Salsabilla Anjani Rizki", "gender" => "female", "birthDate" => "2001-04-16", "ihs" => "P02280547535"],
                ["nik" => "9201076001000007", "nama" => "Theodore Elisjah", "gender" => "female", "birthDate" => "1985-09-18", "ihs" => "P01836748436"],
                ["nik" => "9201394901000008", "nama" => "Sonia Herdianti", "gender" => "female", "birthDate" => "1985-09-18", "ihs" => "P00883356749"],
                ["nik" => "9201076407000009", "nama" => "Nancy Wang Test", "gender" => "female", "birthDate" => "1955-10-10", "ihs" => "P01058967035"],
                ["nik" => "9210060207000010", "nama" => "Syarif Muhammad", "gender" => "male", "birthDate" => "1988-11-02", "ihs" => "P02428473601"],
            ];

            return $patients[array_rand($patients)];
        }

        public static function getRandomPractitioner() {
            $practitioners = [
                ["nik" => "7209061211900001", "nama" => "dr. Alexander", "gender" => "male", "birthDate" => "1994-01-01", "ihs" => "10009880728"],
                ["nik" => "3322071302900002", "nama" => "dr. Yoga Yandika, Sp.A", "gender" => "male", "birthDate" => "1995-02-02", "ihs" => "10006926841"],
                ["nik" => "3171071609900003", "nama" => "dr. Syarifuddin, Sp.Pd.", "gender" => "male", "birthDate" => "1988-03-03", "ihs" => "10001354453"],
                ["nik" => "3207192310600004", "nama" => "dr. Nicholas Evan, Sp.B.", "gender" => "male", "birthDate" => "1986-04-04", "ihs" => "10010910332"],
                ["nik" => "6408130207800005", "nama" => "dr. Dito Arifin, Sp.M.", "gender" => "male", "birthDate" => "1985-05-05", "ihs" => "10018180913"],
                ["nik" => "3217040109800006", "nama" => "dr. Olivia Kirana, Sp.OG", "gender" => "female", "birthDate" => "1984-06-06", "ihs" => "10002074224"],
                ["nik" => "3519111703800007", "nama" => "dr. Alicia Chrissy, Sp.N.", "gender" => "female", "birthDate" => "1982-07-07", "ihs" => "10012572188"],
                ["nik" => "5271002009700008", "nama" => "dr. Nathalie Tan, Sp.PK.", "gender" => "female", "birthDate" => "1981-08-08", "ihs" => "10018452434"],
                ["nik" => "3313096403900009", "nama" => "Sheila Annisa S.Kep", "gender" => "female", "birthDate" => "1980-09-09", "ihs" => "10014058550"],
                ["nik" => "3578083008700010", "nama" => "apt. Aditya Pradhana, S.Farm.", "gender" => "female", "birthDate" => "1980-10-10", "ihs" => "10001915884"],
            ];

            return $practitioners[array_rand($practitioners)];
        }

        public static function uuid($data = null){
            if ($data === null) {
                $data = openssl_random_pseudo_bytes(16);
            }
            assert(strlen($data) == 16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
            return $uuid;
        }

        public static function generatedoauth(){
            $body   = array("client_id"=>self::$clientid,"client_secret"=>self::$clientsecret);
            $header = array("Content-Type: application/x-www-form-urlencoded");

            $responsecurl = curl([
                'url'     => self::$baseurloauth."/accesstoken?grant_type=client_credentials",
                'method'  => "POST",
                'header'  => $header,
                'body'    => http_build_query($body),
                'savelog' => false,
                'source'  => "SATUSEHAT-TOKEN"
            ]);

            return $responsecurl;
        }

        public static function getpatientid($nikktp,$oauth){
            $header = array("Content-Type: application/json","Authorization: Bearer ".$oauth);

            $responsecurl = curl([
                'url'     => self::$baseurl."/Patient?identifier=https://fhir.kemkes.go.id/id/nik|".$nikktp,
                'method'  => "GET",
                'header'  => $header,
                'body'    => "",
                'savelog' => false,
                'source'  => "SATUSEHAT-PATIENT"
            ]);

            return $responsecurl;  
        }

        public static function postbundle($body,$oauth){
            $header = array("Content-Type: application/json","Authorization: Bearer ".$oauth);

            $responsecurl = curl([
                'url'     => self::$baseurlbundle,
                'method'  => "POST",
                'header'  => $header,
                'body'    => $body,
                'savelog' => false,
                'source'  => "SATUSEHAT-BUNDLE"
            ]);

            return $responsecurl;
        }

        public static function getdata($resource,$parameter,$value,$oauth){
            $header = array("Content-Type: application/json","Authorization: Bearer ".$oauth);

            $responsecurl = curl([
                'url'     => self::$baseurl."/".$resource."?".$parameter."=".$value,
                'method'  => "GET",
                'header'  => $header,
                'body'    => "",
                'savelog' => false,
                'source'  => "SATUSEHAT-GET-RESOURCE"
            ]);

            return $responsecurl;    
        }

        public static function getdataid($resource,$value,$oauth){
            $header = array("Content-Type: application/json","Authorization: Bearer ".$oauth);

            $responsecurl = curl([
                'url'     => self::$baseurl."/".$resource."/".$value,
                'method'  => "GET",
                'header'  => $header,
                'body'    => "",
                'savelog' => false,
                'source'  => "SATUSEHAT-GET-RESOURCE"
            ]);

            return $responsecurl;    
        }

        
    }

?>