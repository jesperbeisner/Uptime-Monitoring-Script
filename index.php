<?php

declare(strict_types=1);

const CONFIG = [
    'password' => 'PLACEHOLDER',
    'ntfy-topic' => 'PLACEHOLDER',
    'endpoints' => [
        'https://example.com',
    ],
];

if (!isset($_GET['password']) || $_GET['password'] !== CONFIG['password']) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['Error' => 'The password is not set or does not match.']);
    exit(1);
}

$errors = [];
foreach (CONFIG['endpoints'] as $endpoint) {
    $response = file_get_contents($endpoint, false, stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true,
        ],
    ]));

    if ($response === false) {
        $errors[] = sprintf("The 'file_get_contents' call for endpoint '%s' did return 'false'.", $endpoint);
    } else {
        if (!str_contains($http_response_header[0], '200') && !str_contains($http_response_header[0], '204')) {
            $errors[] = sprintf("The endpoint '%s' did not return a status code of 200 or 204.", $endpoint);
        }
    }
}

if ($errors !== []) {
    file_get_contents(sprintf('https://ntfy.sh/%s', CONFIG['ntfy-topic']), false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: text/plain\r\n" . "Title: Uptime-Monitoring-Script\r\n" . "Priority: urgent\r\n" . "Tags: warning",
            'content' => implode("\n\n", $errors),
        ],
    ]));
}

header('Content-Type: application/json');
echo json_encode(['Success' => "The 'Uptime-Monitoring-Script' ran successfully."]);
exit(0);
