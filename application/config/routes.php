<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    $route['default_controller']   = 'landingpage/Landingpage';
    $route['404_override']         = 'Error';
    $route['translate_uri_dashes'] = FALSE;

    $route['patientid']  = 'restapi/Patient/patientid';
    $route['poliklinik'] = 'restapi/Encounter/poliklinik';
    $route['igd']        = 'restapi/Encounter/igd';

    // $route['anamnesaawalrj']   = 'restapi/Observation/anamnesaawalrj';
    // $route['anamnesaawaligd']  = 'restapi/Observation/anamnesaawaligd';
    $route['psikologisigd']  = 'restapi/Observation/psikologisigd';
    
    // $route['hasillab']         = 'restapi/Observation/hasillab';
    // $route['orderrad']         = 'restapi/Servicerequest/orderrad';
    // $route['orderlab']         = 'restapi/Servicerequest/orderlab';
    // $route['dicom']            = 'restapi/Imagestudy/dicom';
    // $route['specimenlab']      = 'restapi/Specimen/specimenlab';
    // $route['diaglaboratorium'] = 'restapi/Diagnosticreport/laboratorium';
    // $route['careplan']         = 'restapi/Careplan/careplan';
    // $route['radiologi']        = 'restapi/Procedure/radiologi';
    // $route['cipoliklinik']     = 'restapi/Clinicalimpression/cipoliklinik';
    // $route['compoliklinik']    = 'restapi/Composition/compoliklinik';
    // $route['singledose']       = 'restapi/Medication/singledose';
    // $route['singledosereq']    = 'restapi/Medicationrequest/singledose';

    
    // $route['alergipasienigd'] = 'restapi/Allergyintolerance/alergipasienigd';
    
    // $route['qpoliklinik']        = 'restapi/Questionnaireresponse/qpoliklinik';
?>