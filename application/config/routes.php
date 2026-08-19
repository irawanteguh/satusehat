<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    $route['default_controller']   = 'landingpage/Landingpage';
    $route['404_override']         = 'Error';
    $route['translate_uri_dashes'] = FALSE;

    $route['patientid']          = 'restapi/Patient/patientid';
    $route['poliklinik']         = 'restapi/Encounter/poliklinik';
    $route['anamnesaawalrj']     = 'restapi/Observation/anamnesaawalrj';
    $route['hasillab']           = 'restapi/Observation/hasillab';
    $route['orderrad']           = 'restapi/Servicerequest/orderrad';
    $route['orderlab']           = 'restapi/Servicerequest/orderlab';
    $route['dicom']              = 'restapi/Imagestudy/dicom';
    $route['specimenlab']        = 'restapi/Specimen/specimenlab';
    $route['diaglaboratorium']   = 'restapi/Diagnosticreport/laboratorium';
    $route['careplan']           = 'restapi/Careplan/careplan';
    // $route['allergyintolerance'] = 'restapi/Allergyintolerance/allergyintolerance';
    $route['radiologi']          = 'restapi/Procedure/radiologi';
    $route['cipoliklinik']       = 'restapi/Clinicalimpression/cipoliklinik';
    // $route['qpoliklinik']        = 'restapi/Questionnaireresponse/qpoliklinik';
    $route['compoliklinik']      = 'restapi/Composition/compoliklinik';
    $route['singledose']         = 'restapi/Medication/singledose';
    $route['singledosereq']      = 'restapi/Medicationrequest/singledose';
?>