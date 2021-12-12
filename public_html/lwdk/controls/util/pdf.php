<?php
/*
    VER: 1.0
    LAST-UPDATE: 17/03/2021
*/
    /* CONTROLE QUE CARREGA ALGUM PLUGIN */

    function ctrl_util_pdf($args){

        /* EXTENSAO DE CLASSE CASO NECESSARIO */

        (new APPControls)->loadPlugin("fpdf182@fpdf");

        $instance = new class extends HTML2PDF {
            public $width_page = 190;
            const DPI = 96;
            const MM_IN_INCH = 25.4;
            const A4_HEIGHT = 297;
            const A4_WIDTH = 210;
            const MAX_WIDTH = 800;
            const MAX_HEIGHT = 500;

            function __construct(){
                parent::__construct();
            }

            function pixelsToMM($val) {
               return $val * self::MM_IN_INCH / self::DPI;
            }

            function resizeToFit($imgFilename) {
               list($width, $height) = getimagesize($imgFilename);

               $widthScale = self::MAX_WIDTH / $width;
               $heightScale = self::MAX_HEIGHT / $height;

               $scale = min($widthScale, $heightScale);

               return array(
                   round($this->pixelsToMM($scale * $width)),
                   round($this->pixelsToMM($scale * $height))
               );
            }

            function centreImage($img) {
               list($width, $height) = $this->resizeToFit($img);

               // you will probably want to swap the width/height
               // around depending on the page's orientation
               $this->Image(
                   $img, (self::A4_HEIGHT - $width) / 2,
                   (self::A4_WIDTH - $height) / 2,
                   $width,
                   $height
               );
            }

            function database(){
                return $this->args["ux"]->parent()->database;
            }

            function parent(){
                return $this->args["ux"];
            }

            function system(){
                return $this->parent()->parent();
            }

            function Table($header, $data, $mode="equal"){
                switch($mode){
                    case "equal":
                        $fixed_width = floor($this->width_page / count($header));
                    break;
                    default:
                        $fixed_width = 50;
                    break;
                }
            	foreach($header as $col){
                    $this->SetFont('','B', min(11,round(($fixed_width*(($fixed_width*.015)/max(1.75,min(3.8,strlen($col)*.125)))))));
            		$this->Cell($fixed_width,7,iconv('UTF-8', 'windows-1252', ($col)),1,0,"C");
                }
            	$this->Ln();

                $this->SetFont('','',10);

            	foreach($data as $row){
            		foreach($row as $col)
            			$this->Cell($fixed_width,6,iconv('UTF-8', 'windows-1252', $col),1,0,"C");
            		$this->Ln();
            	}
            }

            function InsertImageBase64($dataURI){
                $TEMPIMGLOC = md5(uniqid()) . '.png';

                $dataPieces = explode(',',$dataURI);
                $encodedImg = $dataPieces[1];
                $decodedImg = base64_decode($encodedImg);

                //  Check if image was properly decoded
                if( $decodedImg!==false )
                {
                    //  Save image to a temporary location
                    if( file_put_contents($TEMPIMGLOC,$decodedImg)!==false )
                    {

                        $this->Image($TEMPIMGLOC);

                        //  Delete image from server
                        unlink($TEMPIMGLOC);
                    }
                }
            }

            function Show(){
                $this->Output("I");
            }

            function Download(String $name=""){
                if(empty($name)){
                    $name = date("d-m-Y-H-i-s") . ".pdf";
                }
                $this->Output("D", $name);
            }
        };

        $instance->args = $args;

        $instance->SetFont('Arial','',14);

        return $instance;
    }

// class PDF extends FPDF
// {
// // Load data
// function LoadData($file)
// {
// 	// Read file lines
// 	$lines = file($file);
// 	$data = array();
// 	foreach($lines as $line)
// 		$data[] = explode(';',trim($line));
// 	return $data;
// }
//
// // Simple table
// function BasicTable($header, $data)
// {
// 	// Header
// 	foreach($header as $col)
// 		$this->Cell(40,7,$col,1);
// 	$this->Ln();
// 	// Data
// 	foreach($data as $row)
// 	{
// 		foreach($row as $col)
// 			$this->Cell(40,6,$col,1);
// 		$this->Ln();
// 	}
// }
//
// // Better table
// function ImprovedTable($header, $data)
// {
// 	// Column widths
// 	$w = array(40, 35, 40, 45);
// 	// Header
// 	for($i=0;$i<count($header);$i++)
// 		$this->Cell($w[$i],7,$header[$i],1,0,'C');
// 	$this->Ln();
// 	// Data
// 	foreach($data as $row)
// 	{
// 		$this->Cell($w[0],6,$row[0],'LR');
// 		$this->Cell($w[1],6,$row[1],'LR');
// 		$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
// 		$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
// 		$this->Ln();
// 	}
// 	// Closing line
// 	$this->Cell(array_sum($w),0,'','T');
// }
//
// // Colored table
// function FancyTable($header, $data)
// {
// 	// Colors, line width and bold font
// 	$this->SetFillColor(255,0,0);
// 	$this->SetTextColor(255);
// 	$this->SetDrawColor(128,0,0);
// 	$this->SetLineWidth(.3);
// 	$this->SetFont('','B');
// 	// Header
// 	$w = array(40, 35, 40, 45);
// 	for($i=0;$i<count($header);$i++)
// 		$this->Cell($w[$i],7,$header[$i],1,0,'C',true);
// 	$this->Ln();
// 	// Color and font restoration
// 	$this->SetFillColor(224,235,255);
// 	$this->SetTextColor(0);
// 	$this->SetFont('');
// 	// Data
// 	$fill = false;
// 	foreach($data as $row)
// 	{
// 		$this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
// 		$this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
// 		$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
// 		$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
// 		$this->Ln();
// 		$fill = !$fill;
// 	}
// 	// Closing line
// 	$this->Cell(array_sum($w),0,'','T');
// }
// }
//
// $pdf = new PDF();
// // Column headings
// $header = array('Country', 'Capital', 'Area (sq km)', 'Pop. (thousands)');
// // Data loading
// $data = $pdf->LoadData('countries.txt');
// $pdf->SetFont('Arial','',14);
// $pdf->AddPage();
// $pdf->BasicTable($header,$data);
// $pdf->AddPage();
// $pdf->ImprovedTable($header,$data);
// $pdf->AddPage();
// $pdf->FancyTable($header,$data);
// $pdf->Output();
?>
