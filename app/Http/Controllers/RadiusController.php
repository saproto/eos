<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RadiusController extends Controller
{

    protected $radiusServers = [
        'radius1.utsp.utwente.nl',
        'radius2.utsp.utwente.nl'
    ];

    public function authenticate(Request $request)
    {

        if (!FirewallController::isWhitelisted($request->ip())) {
            abort(403);
        }

        $privateKey = str_replace('_!n_', "\n", env('RADIUS_PRIVATE_KEY'));

        if (!$request->has('challenge')) {
            return 'MISSING_CHALLENGE';
        }

        // Decode received challenge from base64 to ASCII
        $challenge = base64_decode($request->challenge);

        // Decrypts received data using private key, and converts json to object.
        openssl_private_decrypt($challenge, $answer, $privateKey);
        $userData = json_decode($answer);

        $fakeToken = md5(rand()); // Generate random string in same format as token

        if ($userData == null || !property_exists($userData, 'user') || !property_exists($userData, 'password') || !property_exists($userData, 'token')) {
            return 'INVALID_CHALLENGE';
        }

        // Check received data against Radius
        if ($this->radiusAuth($userData->user, $userData->password)) {
            return $userData->token; // If credentials verified, return token
        } else {
            if ($userData->token == $fakeToken) { // Check for ridiculously small chance of fake token matching the real token
                return "INCORRECT_HORSE_BATTERY_STAPLE_EXCEPTION";
            } else {
                return $fakeToken;
            }
        }

    }

    private function radiusAuth($username, $password)
    {

        $radius = radius_auth_open();

        $radiusServerAvailable = false;

        foreach ($this->radiusServers as $radiusServer) {
            if (radius_add_server($radius, $radiusServer, 0, env('RADIUS_SECRET'), 5, 3)) {
                $radiusServerAvailable = true;
            }
        }

        if (!$radiusServerAvailable) {
            return false;
        }

        if (!radius_create_request($radius, RADIUS_ACCESS_REQUEST)) {
            return false;
        }

        radius_put_attr($radius, RADIUS_USER_NAME, $username . '@proto.utwente.nl');
        radius_put_attr($radius, RADIUS_USER_PASSWORD, $password);

        switch (radius_send_request($radius)) {
            case RADIUS_ACCESS_ACCEPT:
                return TRUE;
                break;
            case RADIUS_ACCESS_REJECT:
                return FALSE;
                break;
            case RADIUS_ACCESS_CHALLENGE:
                return FALSE;
                break;
            default:
                return FALSE;
        }

    }

}
