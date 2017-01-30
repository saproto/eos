<?php

namespace App\Http\Controllers;

class FirewallController extends Controller
{

    public static function isWhitelisted($ip)
    {

        $whitelist = [
            '84.245.42.161/32', // Admin
            '84.245.15.31/32', // Admin
            '37.97.129.76/32',   // Atalanta
            env('DEBUG_IP', '0.0.0.0') . '/32'
        ];

        foreach ($whitelist as $range) {
            if (FirewallController::cidr_match($ip, $range)) {
                return true;
            }
        }
        return false;

    }

    public static function isOnCampus($ip)
    {

        $campus = [
            '130.89.0.0/16'     // University Campus
        ];

        foreach ($campus as $range) {
            if (FirewallController::cidr_match($ip, $range)) {
                return true;
            }
        }
        return FirewallController::isWhitelisted($ip);

    }

    private static function cidr_match($ip, $range)
    {
        list ($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
        return ($ip & $mask) == $subnet;
    }

}
