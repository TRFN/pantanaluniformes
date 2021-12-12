<?php
/*
    VER: 1.0
    LAST-UPDATE: 17/03/2021
*/
    /* CONTROLE QUE CARREGA ALGUM PLUGIN */

    function ctrl_util_excel($args){

        /* EXTENSAO DE CLASSE CASO NECESSARIO */

        // (new APPControls)->loadPlugin("fpdf182@fpdf");

        $instance = new class extends APPControls {
            private $TITLE;
            private $EXCEL;

            function __construct(){
                header("Content-Type: text/plain");
                $this->EXCEL = $this->loadPlugin("PHPExcel-1.8@PHPExcel");
                $this->SetDocumentTitle(date("d-m-Y"));
                return $this;
            }

            function Instance(){
                return $this->EXCEL;
            }

            function SetDocumentTitle(String $title=""){
                $this->TITLE = $title;
                $this->EXCEL->getProperties()->setCreator($siteurl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}")
                							 ->setLastModifiedBy($siteurl)
                							 ->setTitle($title)
                							 ->setSubject($title)
                							 ->setDescription($title)
                							 ->setKeywords($title)
                							 ->setCategory($siteurl);
                $this->EXCEL->getActiveSheet()->setTitle($title);
                return $this;
            }

            function Title(String $title){
                $this->EXCEL->getActiveSheet()->setTitle($title);
                return $this;
            }

            function SetFullDocumentHeader(String $title, String $subject, String $description){
                $this->TITLE = $title;
                $this->EXCEL->getProperties()->setCreator($siteurl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}")
                							 ->setLastModifiedBy($siteurl)
                							 ->setTitle($title)
                							 ->setSubject($subject)
                							 ->setDescription($description)
                							 ->setKeywords($title)
                							 ->setCategory($subject);
                $this->EXCEL->getActiveSheet()->setTitle($title);
                return $this;
            }

            function SetPage(int $index){
                $this->EXCEL->setActiveSheetIndex($index);
                return $this;
            }

            function SetCell(String $position, String $value){
                $this->EXCEL->getActiveSheet()->setCellValue($position,$value);
                return $this;
            }

            function CreateFile(String $file, String $format = "xlsx"){
                $f = "Excel2007";

                switch($format){
                    case "xsl":
                        $f = 'Excel5';
                    break;

                    case "xslx":
                        $f = 'Excel2007';
                    break;
                }

                PHPExcel_IOFactory::createWriter($this->EXCEL, $f)->save($file);
            }

            function WriteFormatted(Array $col, int $row, String $text, int $size, bool $bold = false, bool $italic = false){
                $this->Instance()->getActiveSheet()->getRowDimension($row)->setRowHeight(max(20,round($size*1.5)));
                if(count($col) == 2){
                    $this->Instance()->getActiveSheet()->mergeCells("{$col[0]}{$row}:{$col[1]}{$row}");
                } elseif(count($col) > 2 || count($col) == 1) {
                    $col = array_shift($col);
                } elseif(count($col) == 0) {
                    $col = array("A");
                }
                $this->SetCell("{$col[0]}{$row}", $text);
                $this->Instance()->getActiveSheet()->getStyle("{$col[0]}{$row}")->applyFromArray(array('font' => array('bold' => $bold, 'size'=>$size, 'italic'=>$italic),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)));
            }

            function Download(String $format="xlsx"){

                $ext = $format;

                $f = "Excel2007";

                switch($format){
                    case "xsl":
                        $f = 'Excel5';
                    break;

                    case "xslx":
                        $f = 'Excel2007';
                    break;

                    case "csv":
                        $f = 'CSV';
                    break;
                }

                header('Content-type: application/vnd.ms-excel');

                header('Content-Disposition: attachment; filename="' . $this->TITLE . '.' . $ext . '"');

                PHPExcel_IOFactory::createWriter($this->EXCEL, $f)->save('php://output');
                exit;
            }

            function Save(String $format="xlsx",String $dir=""){

                $ext = $format;

                $f = "Excel2007";

                switch($format){
                    case "xsl":
                        $f = 'Excel5';
                    break;

                    case "xslx":
                        $f = 'Excel2007';
                    break;

                    case "csv":
                        $f = 'CSV';
                    break;
                }

                PHPExcel_IOFactory::createWriter($this->EXCEL, $f)->save($dir . $this->TITLE . '.' . $ext);

                return $dir . $this->TITLE . '.' . $ext;
            }
        };

        $instance->args = $args;

        return $instance;
    }
