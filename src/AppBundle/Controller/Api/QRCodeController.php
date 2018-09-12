<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Endroid\QrCode\QrCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class QRCodeController extends Controller
{
    /**
     * @Route("/api/barcode/{text}", name="api_barcode_generate")
     *
     * @param String $text Text to generate as a QRCode
     */
    public function getQRCode($text)
    {
        if (substr($text, 0, 4) !== '$AD-') {
            $text = '$AD-C-'.$text;
        }
        $qrCode = new QrCode($text);
        $qrCode->setSize(150);

        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        die();
    }
}
