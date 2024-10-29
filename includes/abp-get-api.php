<?php
require_once '../../../../wp-load.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$postData = json_decode(file_get_contents('php://input'), true);

$action = $postData['action'] ?? 'getProducts';
$url = '';

switch ($action) {
    case 'getProducts':
        $url = ABP_LICENCE_API_ENDPOINT . 'getProductsByCollection';
        $data = [
            "collectionHandle" => $postData['collectionHandle'] ?? "",
            "productHandle" => $postData['productHandle'] ?? "",
            "paginationParams" => $postData['paginationParams'] ?? [
                "first" => 6,
                "afterCursor" => null,
                "beforeCursor" => null,
                "reverse" => true
            ]
        ];
        break;
        
    case 'getCollections':
        $url = ABP_LICENCE_API_ENDPOINT . 'getCollectionCategories';
        $data = []; 
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

$args = [
    'method'    => 'POST',
    'body'      => json_encode($data),
    'headers'   => [
        'Content-Type' => 'application/json',
    ]
];

$response = wp_remote_post($url, $args);

if (is_wp_error($response)) {
    echo json_encode(['error' => 'Request failed: ' . $response->get_error_message()]);
    exit;
}

$http_code = wp_remote_retrieve_response_code($response);
if ($http_code !== 200) {
    echo json_encode(['error' => 'HTTP error: ' . $http_code]);
    exit;
}

$response_body = wp_remote_retrieve_body($response);
$data = json_decode($response_body, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON format received from the external API']);
    exit;
}

echo json_encode($data);
?>
