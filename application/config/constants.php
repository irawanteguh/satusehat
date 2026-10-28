<?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);
    defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
    defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
    defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
    defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);
    defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
    defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
    defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb');        // truncates existing file data, use with care
    defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b');  // truncates existing file data, use with care
    defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
    defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
    defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
    defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');
    defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0);         // no errors
    defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1);           // generic error
    defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3);          // configuration error
    defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4);    // file not found
    defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5);   // unknown class
    defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6);  // unknown class member
    defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7);      // invalid user input
    defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8);        // database error
    defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9);       // lowest automatically-assigned error code
    defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125);     // highest automatically-assigned error code

    $clientid_dev = "wYiLSpaT4s7GR24ZqGvC1iyG2GBDZeYGEYvDeonE750ahy8h";
    $secretid_dev = "dcBZIfHwKr81OivmudeTJr8411fTJSFRikeNdniISGZ9GrXAvHpsjQlrkumHBXiC";

    $clientid_prod = "sXNpu0gsdKuo0RGMG5ldMLKVUV3xAzLknKuvd4CUfCLFwADU";
    $secretid_prod = "l0J2htvOACE7VEXrc8RrQNouW07mlYNlCAlJcYkGG9NIGXMeOhrrJ3Q38YLAmZaY";

    $orgid_dev  = "5767885d-7142-4d38-a2b8-70da28c277f4";
    $orgid_prod = "100026540";

    $env = 'developmentkemenkes';

    switch ($env) {

        //!!!!!! Dinas Kesehatan
        case 'developmentdki':
            define('SERVER',           'development');
            define('RS_ID',            $orgid_dev);
            define('CLIENT_ID',        $clientid_dev);
            define('CLIENT_SECRET',    $secretid_dev);
            define('OAUTH_URL',       'https://api-kesehatan.jakarta.go.id/rme/dev/oauth2/v1');
            define('BASE_URL',        'https://api-kesehatan.jakarta.go.id/rme/dev/fhir-r4/v1');
            define('BASE_URL_BUNDLE', 'https://api-kesehatan.jakarta.go.id/rme/dev/fhir-r4/v1/Bundle');
            define('BASE_URL_KFA',    'https://api-satusehat-stg.dto.kemkes.go.id/kfa-v2');
            define('CONCENT_URL',     'https://api-kesehatan.jakarta.go.id/rme/dev/consent/v1');
        break;

        case 'productiondki':
            define('SERVER',           'production');
            define('RS_ID',            $orgid_prod);
            define('CLIENT_ID',        $clientid_prod);
            define('CLIENT_SECRET',    $secretid_prod);
            define('OAUTH_URL',       'https://api-kesehatan.jakarta.go.id/rme/oauth2/v1');
            define('BASE_URL',        'https://api-kesehatan.jakarta.go.id/rme/fhir-r4/v1');
            define('BASE_URL_BUNDLE', 'https://api-kesehatan.jakarta.go.id/rme/fhir-r4/v1/Bundle');
            define('BASE_URL_KFA',    'https://api-satusehat.kemkes.go.id/kfa-v2');
            define('CONCENT_URL',     'https://api-kesehatan.jakarta.go.id/rme/consent/v1');
        break;

        //!!!!!! Kementrian Kesehatan
        case 'developmentkemenkes':
            define('SERVER',          'development');
            define('RS_ID',            $orgid_dev);
            define('CLIENT_ID',        $clientid_dev);
            define('CLIENT_SECRET',    $secretid_dev);
            define('OAUTH_URL',       'https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1');
            define('BASE_URL',        'https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1');
            define('BASE_URL_BUNDLE', 'https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1');
            define('BASE_URL_KFA',    'https://api-satusehat-stg.dto.kemkes.go.id/kfa-v2');
            define('CONCENT_URL',     'https://api-satusehat-stg.dto.kemkes.go.id/consent/v1');
        break;

        case 'productionkemenkes':
            define('SERVER',           'production');
            define('RS_ID',            $orgid_prod);
            define('CLIENT_ID',        $clientid_prod);
            define('CLIENT_SECRET',    $secretid_prod);
            define('OAUTH_URL',       'https://api-satusehat.kemkes.go.id/oauth2/v1');
            define('BASE_URL',        'https://api-satusehat.kemkes.go.id/fhir-r4/v1');
            define('BASE_URL_BUNDLE', 'https://api-satusehat.kemkes.go.id/fhir-r4/v1');
            define('BASE_URL_KFA',    'https://api-satusehat.kemkes.go.id/kfa-v2');
            define('CONCENT_URL',     'https://api-satusehat.kemkes.go.id/consent/v1');
        break;
        
    }
    
?>