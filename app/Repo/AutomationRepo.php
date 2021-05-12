<?php

namespace App\Repo;

use App\Models\Automation;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;


class AutomationRepo
{

    public static function AutomationJob(){

        //$a = [];
        //$lastKey = array();

        $automations = [
            'Automation' =>
                    ['name' => 'PR_Aviso_Boleto_Massivo',
                    'customerKey' => '9330a95e-f0f9-b824-49d2-377c85b58629',
                    'dataExtension' => 'TB00_Boleto_Massivo_Prod_Aviso',
                    'dataExtension_CK' => 'B76765BE-07C4-4CF3-A167-E177754E4778'],

                    ['name' => 'Automation_Import_Flat',
                    'customerKey' => 'ecfe9642-463a-fbd0-2466-c9143d70f3f4',
                    'dataExtension' => 'TB00_Flat_Geral',
                    'dataExtension_CK' => 'E87464D0-0750-4DDF-8E66-C5437174D39F'],

                    // ['name' => 'Automation Inadimplente',
                    // 'customerKey' => '3a81ff80-42a2-bb30-83ef-0c995f184ad0',
                    // 'dataExtension' => 'Teste_DE',
                    // 'dataExtension_CK' => 'E87464D0-0750-4DDF-8E66-C5437174D39F']
        ];


        // TOKEN SALESFORCE REST
        $urlAuthenticate = env('URL_AUTH_REST');

        $login['grant_type'] = "client_credentials";
        $login['client_id'] = env('CLIENT_ID');
        $login['client_secret'] = env('CLIENT_SECRET');

        $headerLogin = array(
                    'Content-Type' => 'application/json',
                );

        $responseLogin = Http::withHeaders($headerLogin)->post($urlAuthenticate, $login);
        $statusLogin = $responseLogin->getStatusCode();
        // FIM TOKEN SALESFORCE REST

        $bodyLogin = $responseLogin->getBody()->__toString();
        $returnLogin = json_decode($bodyLogin);


        if($statusLogin == 200){

            //GET DATA EXTENSION ROWS
            foreach($automations as $automation){
                $dataExtension = Http::withHeaders($headerLogin)
                    ->withToken($returnLogin->access_token)
                    ->get('https://mc6ttz-frz9j0jq5lw06m-j0gd9q.rest.marketingcloudapis.com/data/v1/customobjectdata/key/'.$automation['dataExtension_CK'].'/rowset?$page=1&$pagesize=1');
                $bodyDE = $dataExtension->getBody()->__toString();
                $returnDE = json_decode($bodyDE);

                $urlSOAP = env('URL_AUTH_SOAP');
                $headersSOAP = array('headers'=>[
                            'Content-Type' => 'text/xml',
                            'SOAPAction' => 'Retrieve'
                            ]);

                $bodySOAP = '<?xml version="1.0" encoding="utf-8"?>
                <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <s:Header>
                    <fueloauth xmlns="http://exacttarget.com">'. $returnLogin->access_token .'</fueloauth>
                </s:Header>
                <s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <RetrieveRequestMsg xmlns="http://exacttarget.com/wsdl/partnerAPI">
                        <RetrieveRequest>
                            <ObjectType>AutomationInstance</ObjectType>
                            <Properties>Automation</Properties>
                            <Properties>Name</Properties>
                            <Properties>Description</Properties>
                            <Properties>RecurrenceID</Properties>
                            <Properties>CustomerKey</Properties>
                            <Properties>IsActive</Properties>
                            <Properties>CreatedDate</Properties>
                            <Properties>ModifiedDate</Properties>
                            <Properties>Status</Properties>
                                    <Properties>StartTime</Properties>
                                    <Properties>Sequence</Properties>
                                    <Properties>SequenceID</Properties>
                            <Filter xsi:type="SimpleFilterPart">
                            <Property>CustomerKey</Property>
                            <SimpleOperator>equals</SimpleOperator>
                            <Value>'. $automation['customerKey'] .'</Value>
                            </Filter>
                        </RetrieveRequest>
                    </RetrieveRequestMsg>
                </s:Body>
                </s:Envelope>';

                $clientSOAP = new Client();

                $headersSOAP = [
                    'headers' => [
                        'Content-Type' => 'text/xml',
                        'SOAPAction' => 'Retrieve'
                    ],
                    'body' => $bodySOAP,
                ];

                $responseSOAP = $clientSOAP->request("POST", $urlSOAP, $headersSOAP);

                $bodyResponseSOAP = $responseSOAP->getBody();

                $xmlSOAP = new \SimpleXMLElement($bodyResponseSOAP);
                $jsonEncode = json_encode($xmlSOAP->asXML());
                $jsonDecode = json_decode($jsonEncode);

                // SUBSTRING PARA RETIRAR DO XML APENAS A TAG RESPONSE
                $subXML = "<RetrieveResponseMsg>".substr($jsonDecode, strpos($jsonDecode, "<Results"), strrpos($jsonDecode, "</Results>"));
                $subXML2 = substr($subXML, strpos($subXML, "<Results"), strrpos($subXML, "</Results>") + 10);

                $explode = explode("xsi:", $subXML2);
                $implode = implode(" ", $explode);
                $implode = "<RetrieveResponseMsg>" . $implode . ">";

                $xmlSOAP2 = simplexml_load_string($implode) or die("Error: Cannot create object");

                foreach( $xmlSOAP2->Results as $item ){
                    $date = date( 'Y-m-d', strtotime($item->StartTime) );
                    //$dateF = date( 'd-m-Y H:i:s', strtotime($item->StartTime) );
                    $today = date( 'Y-m-d', strtotime( now() ) );
                    $todayMinus = date( 'Y-m-d', strtotime('-1 days', strtotime(now())) );

                    if ($date >= $todayMinus) {
                        $save = new Automation();
                        $save->automation = $item->Name;
                        $save->customerKey = $item->CustomerKey;
                        $save->status = $item->Status;
                        $save->statusMessage = $item->StatusMessage;
                        $save->startTime = $item->StartTime;
                        $save->dataExtension = $automation['dataExtension'];
                        $save->dataExtension_CK = $automation['dataExtension_CK'];
                        $save->dataExtension_count = $returnDE->count;
                        $save->save();
                    }


                    // if( $date == $today && $item->Status == 1 ){
                    //     $a[] = [ 'Response' => [
                    //             'Automation' => $item->Name,
                    //             'Date' => $item->StartTime,
                    //             'Status' => 'Complete'
                    //     ]];
                    // }else if ( $date == $today && $item->Status == 2){
                    //     $a[] = [ 'Response' => [
                    //             'Automation' => $item->Name,
                    //             'Date' => $item->StartTime,
                    //             'Status' => 'Error'
                    //     ]];
                    // }
                }


                //$sortKey = array();
                //$sortKey = asort ($a, 'Date');
                //$sortKey = array_sort($a, 'Date', SORT_ASC);
                //$sortKey = Arr::sortByKeys($a, 'Date');


                // CHECA SE ARRAY NÃO É VAZIA
                //if(!empty($a) ){
                //    array_push($lastKey, array_pop($a));
                //}


                //$a = array();
                //$sortKey = array();

            }// FIM FOREACH


            // $lastKeyjsonEncode = json_encode($lastKey);
            // $lastKeyJsonDecode = json_decode($lastKeyjsonEncode, true);

            // foreach ($automations as $key => $value) {
            //     foreach( $lastKeyJsonDecode as $key2 => $value2 ){
            //         if( in_array ( $value['name'], $lastKeyJsonDecode[$key2]['Response']['Automation']) == FALSE){
            //             $today = date('d-m-Y', strtotime(now()));

            //             echo 'NÃO RODOU DIA: ' . $today . '<br/>';
            //             echo 'Automação: ' . $value['name'];
            //             echo '<br/>';
            //             echo 'CustomerKey: ' . $value['customerKey'];
            //             echo '<br/>' . '<br/>';
            //         }if( in_array ( $value['name'], $lastKeyJsonDecode[$key2]['Response']['Automation']) == TRUE
            //             && $lastKeyJsonDecode[$key2]['Response']['Status'] == 2){
            //             $today = date('d-m-Y', strtotime(now()));

            //             echo 'RODOU COM ERRO DIA: ' . $today . '<br/>';
            //             echo 'Automação: ' . $value['name'];
            //             echo '<br/>';
            //             echo 'CustomerKey: ' . $value['customerKey'];
            //             echo '<br/>' . '<br/>';
            //         }
            //     }
            // }


        }// FIM STATUS LOGIN = 200
    }
}
