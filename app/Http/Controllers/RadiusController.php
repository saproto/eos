<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RadiusController extends Controller
{

    public function auth($username, $password)
    {

        $radius = radius_auth_open();

        $radiusServers = [
            'radius1.utsp.utwente.nl',
            'radius2.utsp.utwente.nl'
        ];

        $radiusServerAvailable = false;

        foreach ($radiusServers as $radiusServer) {
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
