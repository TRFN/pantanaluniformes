<?php
/*
    VER: 1.0
    LAST-UPDATE: 17/03/2021
*/
    /* CONTROLE QUE CARREGA ALGUM PLUGIN */

    function ctrl_util_qrcode($args){

        /* EXTENSAO DE CLASSE CASO NECESSARIO */

        // (new APPControls)->loadPlugin("fpdf182@fpdf");

        $instance = new class extends APPControls {
            function __construct(){
                $this->QRCODE = $this->loadPlugin("phpqrcode@PHPQRCode");
            }

            function Instance(){
                return $this->QRCODE;
            }

            function Draw(String $content){
                return $this->QRCODE->Output($content);
            }
        };

        $instance->args = $args;

        return $instance;
    }
