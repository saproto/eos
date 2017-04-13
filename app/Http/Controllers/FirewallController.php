<?php

namespace App\Http\Controllers;

class FirewallController extends Controller
{

    public static function isWhitelisted($ip)
    {

        $whitelist = explode(',', getenv('IP_WHITELIST'));

        foreach ($whitelist as $range) {
            if (FirewallController::cidr_match($ip, $range)) {
                return true;
            }
        }
        return false;

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
