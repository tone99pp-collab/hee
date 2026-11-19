<?php
function push_to_google_sheets(array $payloadData, array $settings): array {
    $webhook = trim($settings['google_sheet_webhook'] ?? '');
    $sheetId = trim($settings['google_sheet_id'] ?? '');

    if ($webhook === '') {
        return ['success' => false, 'message' => 'ຍັງບໍ່ໄດ້ຕັ້ງຄ່າ Google Sheets Webhook URL'];
    }

    $jsonBody = json_encode([
        'sheet_id' => $sheetId,
        'hotel_name' => $settings['hotel_name'] ?? 'Hotel System',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $payloadData
    ], JSON_UNESCAPED_UNICODE);

    if (function_exists('curl_init')) {
        $ch = curl_init($webhook);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => 'cURL Error: ' . $err];
        }
        return [
            'success' => ($code >= 200 && $code < 300),
            'message' => "HTTP {$code}: " . ($resp ?: 'Sent to Google Sheets')
        ];
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $jsonBody,
            'timeout' => 10
        ]
    ]);
    $res = @file_get_contents($webhook, false, $ctx);
    return [
        'success' => $res !== false,
        'message' => $res !== false ? 'Sent to Google Sheets' : 'Webhook request failed'
    ];
}
