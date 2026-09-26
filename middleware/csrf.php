<?php
/**
 * PaySim - CSRF Protection Middleware Guard
 */

require_once dirname(__DIR__) . '/utils/Security.php';
require_once dirname(__DIR__) . '/utils/Response.php';

function checkCsrf(bool $isApi = false): void {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        $token = $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? $_SERVER['HTTP_CSRF_TOKEN']
            ?? null;

        // Also check raw json body if JSON payload
        if (!$token) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (is_array($input) && !empty($input['csrf_token'])) {
                $token = $input['csrf_token'];
            }
        }

        // If in API and API client sends X-Requested-With without CSRF token in testing/demo mode, allow flexibility or enforce strictly
        if (!Security::verifyCsrfToken($token)) {
            // For pure testing script bypass if flag set
            if (defined('BYPASS_CSRF_FOR_TESTS') && BYPASS_CSRF_FOR_TESTS === true) {
                return;
            }

            if ($isApi) {
                Response::forbidden('Invalid or expired CSRF token.');
            } else {
                http_response_code(403);
                die('PaySim Security: CSRF validation failed. Please refresh the page and try again.');
            }
        }
    }
}
