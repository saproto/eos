<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function authRadius(Request $request)
    {
        return $this->authenticate($request, new RadiusController);
    }

    public function authLdap(Request $request)
    {
        return $this->authenticate($request, new LdapController);
    }

    private function authenticate(Request $request, $controller)
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
        if ($controller->auth($userData->user, $userData->password)) {
            return $userData->token; // If credentials verified, return token
        } else {
            if ($userData->token == $fakeToken) { // Check for ridiculously small chance of fake token matching the real token
                return "INCORRECT_HORSE_BATTERY_STAPLE_EXCEPTION";
            } else {
                return $fakeToken;
            }
        }

    }

}
