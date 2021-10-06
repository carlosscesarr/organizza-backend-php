<?php

namespace App\Http\Middleware;

class Api {
    public function handle($request, $response, $next) {
        /*
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $response->addHeader('Access-Control-Allow-Origin', '*');
            $response->addHeader('Access-Control-Allow-Headers', 'Origin, Authorization, X-Auth-Token, X-Requested-With, Content-Type, Accept');
            $response->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->addHeader('Access-Control-Allow-Credentials', 'true');
            $response->addHeader('Access-Control-Max-Age', '86400');    // cache for 1 day
        }
    
        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                // may also be using PUT, PATCH, HEAD etc
                $response->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            }
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                $response->addHeader('Access-Control-Allow-Headers', 'Origin, Authorization, X-Requested-With, Content-Type, Accept');
            }
            exit(0);
        }
        */
        
        return $next($request, $response);
    }
}