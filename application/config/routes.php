<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    $route['default_controller']   = 'landingpage/Landingpage';
    $route['404_override']         = 'Error';
    $route['translate_uri_dashes'] = FALSE;

    // $route['OAuth']                       = 'restapi/OAuth/generatedoauth';
    // $route['Patientid']                   = 'restapi/Patient/patientid';
    // $route['Outpatient']                  = 'restapi/Encounter/rawatjalan';
    // $route['Anamnesaawalrj']              = 'restapi/Observation/anamnesaawalrj';
    // $route['Tindakanrj']                  = 'restapi/Procedure/tindakanrj';
    // $route['Medicationrequestsingledose'] = 'restapi/Medicationrequest/singledose';


    // $route['servicerequestlab']   = 'restapi/Servicerequest/servicerequestlab';
    // $route['servicerequestrad']   = 'restapi/Servicerequest/servicerequestrad';
    // $route['specimenlab']         = 'restapi/Specimen/specimenlab';
    // $route['hasillab']            = 'restapi/Observation/hasillab';
    // $route['diagnosticreportlab'] = 'restapi/Diagnosticreport/diagnosticreportlab';

    

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
    $route['allergyintolerance'] = 'restapi/Allergyintolerance/allergyintolerance';
    $route['radiologi']          = 'restapi/Procedure/radiologi';
?>