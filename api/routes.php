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


            default:
                // RESPONSE FOR UNSUPPORTED REQUESTS
                echo "No Such Request";
                http_response_code(403);
                break;
        }
        break;


    case 'POST':
        // RETRIEVE AND DECODE DATA FROM THE BODY
        $data = json_decode(file_get_contents("php://input"));
        switch ($request[0]) {

            case 'adduser':
                echo json_encode($post->addUser($data));
                break;

            case 'login':
                echo json_encode($post->userLogin($data));
                break;

            case 'addtenant':
                // $user = $auth->authenticateRequest();
                echo json_encode($post->addTenant($data));
                break;

            case 'renewlease':
                // $user = $auth->authenticateRequest();
                echo json_encode($post->renewLease($data));
                break;

            case 'endlease':
                // $user = $auth->authenticateRequest();
                echo json_encode($post->endLease($data));
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

