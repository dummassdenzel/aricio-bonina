<?php
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:4200',
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/*API Endpoint Router*/

require_once "./modules/get.php";
require_once "./modules/post.php";
require_once "./modules/delete.php";
require_once "./config/database.php";
require_once "./vendor/autoload.php";
require_once "./src/Jwt.php";
require_once "./src/AuthMiddleware.php";

// INITIALIZE ESSENTIAL OBJECTS
$con = new Connection();
$pdo = $con->connect();
$get = new Get($pdo);
$post = new Post($pdo);
$delete = new Delete($pdo);
$auth = new AuthMiddleware();


// Check if 'request' parameter is set in the request
if (isset($_REQUEST['request'])) {
    // Split the request into an array based on '/'
    $request = explode('/', $_REQUEST['request']);
} else {
    // If 'request' parameter is not set, return a 404 response
    echo "Not Found";
    http_response_code(404);
}

// THIS IS THE MAIN SWITCH STATEMENT
switch ($_SERVER['REQUEST_METHOD']) {
    case 'OPTIONS':
        http_response_code(200);
        exit();

    case 'GET':
        switch ($request[0]) {
            case 'users':
                // ENDPOINT PROTECTION
                $user = $auth->authenticateRequest();
                if (count($request) > 1) {
                    echo json_encode($get->get_users($request[1]));
                } else {
                    echo json_encode($get->get_users());
                }
                break;

            case 'units':
                // ENDPOINT PROTECTION
                // $user = $auth->authenticateRequest();
                if (count($request) > 1) {
                    echo json_encode($get->get_units($request[1]));
                } else {
                    echo json_encode($get->get_units());
                }
                break;
            case 'tenants':
                // ENDPOINT PROTECTION
                // $user = $auth->authenticateRequest();
                if (count($request) > 1) {
                    echo json_encode($get->get_tenants($request[1]));
                } else {
                    echo json_encode($get->get_tenants());
                }
                break;
            case 'billings':
                // ENDPOINT PROTECTION
                // $user = $auth->authenticateRequest();
                if (count($request) > 1) {
                    echo json_encode($get->get_billings($request[1]));
                } else {
                    echo json_encode($get->get_billings());
                }
                break;

            case 'dashboard-stats':
                // ENDPOINT PROTECTION
                // $user = $auth->authenticateRequest();
                if (count($request) > 1) {
                    echo json_encode($get->get_dashboard_stats());
                } else {
                    echo json_encode($get->get_dashboard_stats());
                }
                break;

            case 'lease-history':
                // ENDPOINT PROTECTION
                // $user = $auth->authenticateRequest();
                if (count($request) > 1) {
                    echo json_encode($get->getLeaseHistory($request[1]));
                } else {
                    echo json_encode($get->getLeaseHistory());
                }
                break;

            case 'uploads':
                if (count($request) > 2 && $request[1] === 'tenant_ids') {
                    $filename = $request[2];
                    $filepath = "uploads/tenant_ids/" . $filename;

                    if (file_exists($filepath)) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $filepath);
                        finfo_close($finfo);

                        header('Content-Type: ' . $mime_type);
                        header('Content-Disposition: inline; filename="' . $filename . '"');
                        readfile($filepath);
                        exit;
                    } else {
                        http_response_code(404);
                        echo "File not found";
                    }
                }
                break;

            default:
                // RESPONSE FOR UNSUPPORTED REQUESTS
                echo "No Such Request";
                http_response_code(403);
                break;
        }
        break;


    case 'POST':
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $data = $post->getRequestData();
        } elseif (strpos($contentType, 'multipart/form-data') !== false) {
            $data = $_POST;
            $files = $_FILES;
        } else {
            echo "Unsupported Content Type";
            http_response_code(415);
            exit();
        }
        switch ($request[0]) {

            case 'adduser':
                echo json_encode($post->addUser($data));
                break;

            case 'login':
                echo json_encode($post->userLogin($data));
                break;

            case 'addtenant':
                // ENDPOINT PROTECTION
                // $user = $auth->authenticateRequest();
                echo json_encode($post->addTenant($data, $files ?? null));
                break;

            case 'renewlease':
                // $user = $auth->authenticateRequest();
                echo json_encode($post->renewLease($data));
                break;

            case 'endlease':
                // $user = $auth->authenticateRequest();
                echo json_encode($post->endLease($data));
                break;

            case 'updateValidId':
                // ENDPOINT PROTECTION
                // $user = $auth->authenticateRequest();
                if (strpos($contentType, 'multipart/form-data') !== false) {
                    echo json_encode($post->updateValidId($_POST['tenant_id'], $_FILES['valid_id']));
                } else {
                    echo "Invalid content type";
                    http_response_code(415);
                }
                break;

            case 'forgot-password':
                echo json_encode($post->forgotPassword($data));
                break;

            case 'reset-password':
                echo json_encode($post->resetPassword($data));
                break;

            case 'update-deposit':
                echo json_encode($post->updateDeposit($data));
                break;

            default:
                // RESPONSE FOR UNSUPPORTED REQUESTS
                echo "No Such Request";
                http_response_code(403);
                break;
        }
        break;

    case 'DELETE':
        switch ($request[0]) {
            case 'deleteuser':
                if (isset($request[1])) {
                    echo json_encode($delete->delete_user($request[1]));
                } else {
                    echo "Submission ID not provided";
                    http_response_code(400);
                }
                break;

            default:
                // Return a 403 response for unsupported requests
                echo "No Such Request";
                http_response_code(403);
                break;
        }
        break;

    default:
        // Return a 404 response for unsupported HTTP methods
        echo "Unsupported HTTP method";
        http_response_code(404);
        break;
}

