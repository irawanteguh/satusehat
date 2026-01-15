<?php

    function curl($config){
        $ci = &get_instance();
        $ci->load->model('Modelserviceapi', 'mlog');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $config['url'],
            CURLOPT_CUSTOMREQUEST  => $config['method'],
            CURLOPT_POSTFIELDS     => $config['body'],
            CURLOPT_HTTPHEADER     => $config['header'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ));

        $response              = curl_exec($curl);
        $response_header       = json_encode(curl_getinfo($curl));
        $request_headers       = json_encode(function_exists('apache_request_headers') ? apache_request_headers() : array());
        $request_url           = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        $response_status       = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $appconnect_time_us    = json_encode(curl_getinfo($curl)['appconnect_time_us']);
        $connect_time_us       = json_encode(curl_getinfo($curl)['connect_time_us']);
        $namelookup_time_us    = json_encode(curl_getinfo($curl)['namelookup_time_us']);
        $pretransfer_time_us   = json_encode(curl_getinfo($curl)['pretransfer_time_us']);
        $redirect_time_us      = json_encode(curl_getinfo($curl)['redirect_time_us']);
        $starttransfer_time_us = json_encode(curl_getinfo($curl)['starttransfer_time_us']);
        $total_time_us         = json_encode(curl_getinfo($curl)['total_time_us']);

        curl_close($curl);

        $responseerror = json_decode($response, true);
        $configBody    = json_decode($config['body'], true);
        $episodeid     = null;
        $resourcetype  = null;

        if(isset($configBody['entry'][0]['resource']['identifier'])){
            if(is_array($configBody['entry'][0]['resource']['identifier']) && isset($configBody['entry'][0]['resource']['identifier'][0])){ 
                $episodeid = isset($configBody['entry'][0]['resource']['identifier'][0]['value']) ? $configBody['entry'][0]['resource']['identifier'][0]['value'] : null;
            }else{
                $episodeid = isset($configBody['entry'][0]['resource']['identifier']['value']) ? $configBody['entry'][0]['resource']['identifier']['value'] : null;
            }

            if(is_array($configBody['entry'][0]['resource']['resourceType'])){ 
                $resourcetype = isset($configBody['entry'][0]['resource']['resourceType']) ? $configBody['entry'][0]['resource']['resourceType'] : null;
            }
        }

        if(isset($responseerror['issue'])){
            $status        = isset($responseerror['text']['status']) ? $responseerror['text']['status'] : null;

            foreach ($responseerror['issue'] as $a) {
                if($a['code']!="duplicate" || $a['code']!="Invalid access token" || $a['code']!="throttled"){
                    $issuelog = [
                        'REQUEST_ID'    => round(microtime(true) * 1000),
                        'RESOURCE_TYPE' => $resourcetype,
                        'STATUS'        => $status,
                        'SEVERITY'      => isset($a['severity']) ? $a['severity'] : null,
                        'CODE'          => isset($a['code']) ? $a['code'] : null,
                        'DETAILS'       => isset($a['details']['text']) ? $a['details']['text'] : null,
                        'EXPRESSION'    => isset($a['expression'][0]) ? $a['expression'][0] : null,
                        'DIAGNOSTIC'    => isset($a['diagnostics']) ? $a['diagnostics'] : null,
                        'SOURCE'        => "MIDDLEWARE",
                        'TRANS_ID'      => $episodeid
                    ];
                    $ci->mlog->saveissuelog($issuelog);
                }
                
            }
        }
        
        return json_decode($response,true);
    }


?>