<?php
class Test extends CI_Controller {
public function index(){
    /* =========================
    * DUMMY VARIABLE (WAJIB)
    * ========================= */

    // Data pemeriksaan (TANPA DB)
    $pen = [
        "uuid"          => "9f3a8c6e-12ab-4cde-9f01-123456789abc",
        "loincCode"     => "36626-0",
        "loincDisplay"  => "Chest X-ray",
        "Klinis"        => "Batuk kronis, suspek TB",
        "tglReq"        => date('Y-m-d'),
        "jamReq"        => date('H:i:s'),
        "idPerformer"   => "10000000001",
        "namaPerformer" => "dr. Radiologi Dummy"
    ];

    // Variable pendukung lainnya (DUMMY)
    $NoPendaftaranidentifier = "REG-20251215-0001";
    $nomoracsn              = "ACSN-001-2025";
    $endpointSr             = "http://loinc.org";
    $idhspasien             = "P000000123";
    $uidencounter           = "E000000456";
    $idhsdokter             = "D000000789";
    $ihsnamadokter           = "dr. Andi Dummy";

    /* =========================
    * VALIDASI
    * ========================= */

    if (!isset($pen) || !is_array($pen)) {
        show_error('Data pemeriksaan ($pen) tidak tersedia', 500);
    }

    /* =========================
    * PAYLOAD SATUSEHAT
    * ========================= */

    $payload = [
        "fullUrl" => "urn:uuid:" . $pen['uuid'],
        "resource" => [
            "resourceType" => "ServiceRequest",
            "identifier" => [
                [
                    "system" => "http://sys-ids.kemkes.go.id/servicerequest/idorg",
                    "value"  => $NoPendaftaranidentifier
                ],
                [
                    "use" => "usual",
                    "type" => [
                        "coding" => [[
                            "system" => "http://terminology.hl7.org/CodeSystem/v2-0203",
                            "code"   => "ACSN"
                        ]]
                    ],
                    "system" => "http://sys-ids.kemkes.go.id/acsn/idorg",
                    "value"  => $nomoracsn
                ]
            ],
            "status"   => "active",
            "intent"   => "original-order",
            "priority" => "routine",
            "category" => [[
                "coding" => [[
                    "system"  => "http://snomed.info/sct",
                    "code"    => "363679005",
                    "display" => "Imaging"
                ]]
            ]],
            "code" => [
                "coding" => [[
                    "system"  => $endpointSr,
                    "code"    => $pen['loincCode'],
                    "display" => $pen['loincDisplay']
                ]],
                "text" => $pen['Klinis']
            ],
            "subject" => [
                "reference" => "Patient/" . $idhspasien
            ],
            "encounter" => [
                "reference" => "Encounter/" . $uidencounter
            ],
            "occurrenceDateTime" =>
                $pen['tglReq'] . 'T' . $pen['jamReq'] . '+00:00',
            "requester" => [
                "reference" => "Practitioner/" . $idhsdokter,
                "display"   => $ihsnamadokter
            ],
            "performer" => [[
                "reference" => "Practitioner/" . $pen['idPerformer'],
                "display"   => $pen['namaPerformer']
            ]],
            "reasonCode" => [[
                "text" => $pen['Klinis']
            ]]
        ],
        "request" => [
            "method" => "POST",
            "url"    => "ServiceRequest"
        ]
    ];

    $payloadJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $payloadJson;
    /* =========================
    * DEBUG (OPTIONAL)
    * ========================= */
    // echo '<pre>' . $payloadJson . '</pre>'; exit;

}
}