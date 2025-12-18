<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhooks extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    public function index()
    {
        // 1. Ambil raw input
        $input = file_get_contents('php://input');

        if (empty($input)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'No data received'
            ]);
            exit;
        }

        // 2. Decode JSON
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid JSON format',
                'error' => json_last_error_msg()
            ]);
            exit;
        }

        // 3. Payload log
        $logFile = APPPATH . 'logs/webhook_log.json';
        $payload = [
            'received_at' => date('Y-m-d H:i:s'),
            'data'        => $data,
            'ip'          => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'user_agent'  => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''
        ];

        // 4. Baca file log (PHP 5.6 SAFE)
        if (file_exists($logFile)) {
            $existing = json_decode(file_get_contents($logFile), true);
            if (!is_array($existing)) {
                $existing = [];
            }
        } else {
            $existing = [];
        }

        $existing[] = $payload;

        // 5. Simpan log
        file_put_contents(
            $logFile,
            json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // 6. Response sukses
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Webhook processed successfully',
            'received_data_count' => is_array($data) ? count($data) : 1
        ]);

        // 7. System log
        error_log('[WEBHOOK] ' . json_encode($payload));
    }
}
