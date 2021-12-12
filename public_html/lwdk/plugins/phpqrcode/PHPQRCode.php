<?php class PHPQRCode {
    function __construct(){
        require_once "qrlib.php";
    }

    function Output(String $content){
        header("Content-Type: image/png");
        QRcode::png($content, "php://output", QR_ECLEVEL_H, 16);
    }
}
