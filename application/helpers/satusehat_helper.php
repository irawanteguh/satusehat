<?php
    define('WIDTH_TIMESTAMP', 21);
    define('WIDTH_PASIEN_ID', 11);
    define('WIDTH_NO_IDENTITAS', 18);
    define('WIDTH_NAMA_PASIEN', 50);
    define('WIDTH_CONSENT_DATE', 22);
    define('WIDTH_SATUSEHATID', 38);
    define('WIDTH_RESOURCE_TYPE', 20);

    
    
    function headerpasien(){
        echo PHP_EOL;
        echo color('cyan').str_pad("TIMESTAMP", WIDTH_TIMESTAMP).str_pad("PASIEN_ID", WIDTH_PASIEN_ID).str_pad("NO_IDENTITAS", WIDTH_NO_IDENTITAS).str_pad("NAMA_PASIEN", WIDTH_NAMA_PASIEN).str_pad("GENERAL_CONSENT_DATE", WIDTH_CONSENT_DATE).str_pad("SATUSEHAT_ID", WIDTH_SATUSEHATID)."MESSAGE".PHP_EOL;
    }

    function headerbundle(){
        echo PHP_EOL;
        echo color('cyan').str_pad("TIMESTAMP", WIDTH_TIMESTAMP).str_pad("PASIEN_ID", WIDTH_PASIEN_ID).str_pad("EPISODE_ID", WIDTH_NO_IDENTITAS).str_pad("RESOURCE_TYPE", WIDTH_RESOURCE_TYPE).str_pad("SATUSEHAT_ID", WIDTH_SATUSEHATID)."MESSAGE".PHP_EOL;
    }

    function formatlogpasien($parameter1,$parameter2,$parameter3,$parameter4,$parameter5,$message,$colorIdentity='cyan'){
        $reset = color('reset');

        $formatted  = color($colorIdentity) . str_pad(date('Y-m-d H:i:s'), WIDTH_TIMESTAMP) . $reset;
        $formatted .= color($colorIdentity) . str_pad($parameter1, WIDTH_PASIEN_ID) . $reset;
        $formatted .= color($colorIdentity) . str_pad($parameter2, WIDTH_NO_IDENTITAS) . $reset;
        $formatted .= color($colorIdentity) . str_pad($parameter3, WIDTH_NAMA_PASIEN) . $reset;
        $formatted .= color($colorIdentity) . str_pad($parameter4, WIDTH_CONSENT_DATE) . $reset;
        $formatted .= color($colorIdentity) . str_pad($parameter5, WIDTH_SATUSEHATID) . $reset;
        $formatted .= color($colorIdentity) . $message . $reset;

        return $formatted . PHP_EOL;
    }

    function formatlogbundle($parameter1,$parameter2,$parameter3,$parameter4,$message,$colorIdentity='cyan'){
        $reset = color('reset');

        $formatted  = color($colorIdentity) . str_pad(date('Y-m-d H:i:s'), WIDTH_TIMESTAMP) . $reset;
        $formatted .= color($colorIdentity) . str_pad($parameter1, WIDTH_PASIEN_ID) . $reset;
        $formatted .= color($colorIdentity) . str_pad($parameter2, WIDTH_NO_IDENTITAS) . $reset;
        $formatted .= color($colorIdentity) . str_pad($parameter3, WIDTH_RESOURCE_TYPE) . $reset;
        $formatted .= color($colorIdentity) . str_pad($parameter4, WIDTH_SATUSEHATID) . $reset;
        $formatted .= color($colorIdentity) . $message . $reset;

        return $formatted . PHP_EOL;
    }

    


?>