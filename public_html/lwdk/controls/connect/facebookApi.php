<?php

function ctrl_connect_facebookApi(){
	/**
	* @return [anonymous] class extended to APPControls model
	*/
    return new class extends APPControls {

		/**
		* Credits Section
		* @credits https://support.google.com/merchants/answer/6324436?hl=pt-BR
		* @credits https://www.seabreezecomputers.com/excel2array/
		*/

		private $excel = null;
		private $gpc   = null;
		public  $data  = null;

		function __construct(){
			$this->data  = [];
			$this->excel = new class {
				private $TITLE;
	            private $EXCEL;
				public $parent;

	            function BootInstance(){
	                $this->EXCEL = $this->parent->loadPlugin("PHPExcel-1.8@PHPExcel");
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
			};
			$this->excel->parent = $this;
			$this->excel->BootInstance();
			$this->gpc = $this->loadPlugin("Google@GProductCategory");
		}

		function render(){
			$this->excel->SetDocumentTitle(substr(sha1(date("dmYHis")), 0, 31));
			$this->excel->Instance()->getActiveSheet()->fromArray($this->data);
			$this->excel->Download("csv");
		}

		function gpc(Array $find){
			return $this->gpc->find($find);
		}

		function setData(Array $source, Array $translate){
			$this->data = array(
				explode(",",
					"id,title,description,availability,condition,price,link,image_link,brand,google_product_category"
				)
			);

			foreach($source as $product){
				$pdata = [];
				foreach($this->data[0] as $pkey){
					if(!isset($translate[$pkey])){
						$pdata[] = "";
					} elseif($pkey == "google_product_category") {
						if(!isset($product[$translate[$pkey]])){
							$pgpc = [];
							foreach($product as $dproduct){
								if(is_string($dproduct)){
									$pgpc = array_merge($pgpc, explode(" ", $dproduct));
								}
							}
							$pdata[] = $this->gpc($pgpc);
						} else {
							$pdata[] = is_array($product[$translate[$pkey]])
								? $this->gpc($product[$translate[$pkey]])
								: $product[$translate[$pkey]];
						}
					} else {
						$pdata[] = $product[$translate[$pkey]];
					}
				}
				$this->data[] = $pdata;
			}
		}
	};
}
