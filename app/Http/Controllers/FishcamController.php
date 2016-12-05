<?php

namespace App\Http\Controllers;

class FishcamController extends Controller
{

    public function getStream()
    {

        header("Content-Transfer-Encoding: binary");
        header("Content-Type: multipart/x-mixed-replace; boundary=video-boundary--");
        header('Cache-Control: no-cache');

        $handle = fopen(env("FISHCAM_URL"), "r");

        while ($data = fread($handle, 8192)) {
            echo $data;
            ob_flush();
            flush();
            set_time_limit(0);
        }

    }

}
