<?php
/**
*Class: BarcodeEAN;
*Function : Draw barcode with EAN8/13 standard;
*Public functions: SetValue, ShowBarcode;
*Private functions: CheckBarNumber, DrawBarcode, GetBarArray;
*Functions detail£º
*	SetValue: set the value of barcode;
*	ShowBarcode: show the value of barcode;
*	CheckBarNumber: check the value of barcode is valid or not;
*	DrawBarcode: draw barcode;
*	GetBarArray: get the binary sequence of barcode (1: black, 0: white);
*Author: sc-zhang;
**/

class BarcodeEAN{
	private $barNumber;
	private $magHeight;
	private $magWidth;
	private $dpi;
	private $showNumber;
	
	function __construct(){
        // Init parameters
		$this->barNumber="0000000000000";
		$this->magHeight=1;
		$this->magWidth=1;
		$this->dpi=100;
		$this->showNumber=1;
	}

	public function SetValue($_barNumber,$_magHeight,$_magWidth,$_dpi,$_showNumber){
        // Set parameters
		$this->barNumber=$_barNumber;
		$this->magHeight=$_magHeight;
		$this->magWidth=$_magWidth;
		$this->dpi=$_dpi;
		$this->showNumber=$_showNumber;
	}

	public function ShowBarcode(){
		header("Content-type:image/png");
		$barType=strlen($this->barNumber);
		header("Content-Disposition:attachment;filename=EAN$barType-$this->barNumber.png");
        
		if(self::CheckBarNumber()){
            // if barcode is valid, draw it
			$im=self::DrawBarcode();
		}
		else{
            // display error image
			$im=imagecreatefromjpeg('../images/error.jpg');
			$sizeImage=getimagesize('../images/error.jpg');
			$imWidth=$sizeImage['0'];
			$imHeight=$sizeImage['1'];
			if($this->magHeight>$this->magWidth){
				$imWidth=$this->magWidth*$this->dpi/100*$imWidth;
				$imHeight=$this->magWidth*$this->dpi/100*$imHeight;
			}
			else{
				$imWidth=$this->magHeight*$this->dpi/100*$imWidth;
				$imHeight=$this->magHeight*$this->dpi/100*$imHeight;
			}
			$imNew=imagecreatetruecolor($imWidth,$imHeight);
			imagecopyresampled($imNew,$im,0,0,0,0,$imWidth,$imHeight,$sizeImage['0'],$sizeImage['1']);
			$im=$imNew;
		}
		imagepng($im);
		imagedestroy($im);
	}

	private function CheckBarNumber(){
		$sumOdd=0;
		$sumEve=0;
		if(strlen($this->barNumber)==13){
			for($i=0;$i<11;$i+=2){
				$sumOdd+=$this->barNumber[$i];
				$sumEve+=$this->barNumber[$i+1];
			}
		}
		else{
			for($i=0;$i<6;$i+=2){
				$sumOdd+=$this->barNumber[$i+1];
				$sumEve+=$this->barNumber[$i];				
			}
			$sumEve+=$this->barNumber[6];
		}
		$sumCheck=$sumOdd+$sumEve*3;
		$sumCheck=(10-$sumCheck%10)%10;
		if($sumCheck==$this->barNumber[strlen($this->barNumber)-1])return true;
		else return false;
	}

	private function DrawBarcode(){
		$barSet=self::GetBarArray();
		
		$lengthNumber=strlen($this->barNumber);

		$singleWidth=0.033*$this->magWidth*$this->dpi;

		$barHeightLong=2.45*$this->magHeight*$this->dpi;
		$barHeightShort=2.285*$this->magHeight*$this->dpi;
		if($this->showNumber){
			$textSize=0.275*$this->magWidth*$this->dpi;
		}
		else{
			$textSize=0.1*$this->magHeight*$this->dpi;
		}
		$textHeight=$barHeightLong+$textSize/2;
		
		if($lengthNumber==13){
			$imageWidth=113*$singleWidth;
		}
		else{
			$imageWidth=88*$singleWidth;
		}
		$imageHeight=$barHeightLong+$textSize*2;
		
		$barTop=$textSize;

		$imageArea=imagecreatetruecolor($imageWidth,$imageHeight);
		$white=imagecolorallocate($imageArea,255,255,255);
		$black=imagecolorallocate($imageArea,0,0,0);

		imagefill($imageArea,0,0,$white);
		
		//add left blank
		if($lengthNumber==13){
			$posBar=11*$singleWidth;
		}
		else{
			$posBar=7*$singleWidth;
		}
		
		//draw barcode
		for($i=0;$i<strlen($barSet);$i++){
			if($barSet[$i]=="1"){
				if($i<3||$i>((int)($lengthNumber/2)*7+2)&&$i<((int)($lengthNumber/2)*7+8)||$i>((int)($lengthNumber/2)*2*7+7)){
					imagefilledrectangle($imageArea,$posBar,$barTop,$posBar+$singleWidth,$barHeightLong,$black);
				}
				else{
					imagefilledrectangle($imageArea,$posBar,$barTop,$posBar+$singleWidth,$barHeightShort,$black);
				}
			}
			$posBar+=$singleWidth;
		}

		if($this->showNumber){
			for($i=0;$i<$lengthNumber;$i++){
				if($lengthNumber==13){				
					if($i==0){
						$posText=1.815*$singleWidth;
					}
					elseif($i<7){
						$posText=(14+($i-1)*7)*$singleWidth;
					}
					else{
						$posText=(61+($i-7)*7)*$singleWidth;
					}
				}
				else{
					if($i<4){
						$posText=(17+($i-1)*7)*$singleWidth;
					}
					else{
						$posText=(43+($i-4)*7)*$singleWidth;
					}
				}
				imagettftext($imageArea,$textSize,0,$posText,$textHeight,$black,"../fonts/OCR-B 10 BT.ttf",$this->barNumber[$i]);
			}
		}

		return $imageArea;
	}

	private function GetBarArray(){
		$leftSetArray=array("AAAAAA","AABABB","AABBAB","AABBBA","ABAABB","ABBAAB","ABBBAA","ABABAB","ABABBA","ABBABA");
		$aSetArray=array("0001101","0011001","0010011","0111101","0100011","0110001","0101111","0111011","0110111","0001011");
		$bSetArray=array("0100111","0110011","0011011","0100001","0011101","0111001","0000101","0010001","0001001","0010111");
		$cSetArray=array("1110010","1100110","1101100","1000010","1011100","1001110","1010000","1000100","1001000","1110100");
		
		$startSet="101";
		$endSet="101";
		$midSet="01010";
		
		$barSet=$startSet;
		if(strlen($this->barNumber)==13){
			$firstLetter=$this->barNumber[0];
			$leftSet=$leftSetArray[$firstLetter];
			for($i=0;$i<strlen($leftSet);$i++){
				if($leftSet[$i]=="A"){
					$temp=$aSetArray[$this->barNumber[$i+1]];
				}
				else{
					$temp=$bSetArray[$this->barNumber[$i+1]];
				}
				$barSet.=$temp;
			}
			$barSet.=$midSet;
			for($i=0;$i<6;$i++){
				$barSet.=$cSetArray[$this->barNumber[$i+7]];
			}
		}
		else{
			for($i=0;$i<4;$i++){
				$barSet.=$aSetArray[$this->barNumber[$i]];
			}
			$barSet.=$midSet;
			for($i=4;$i<8;$i++){
				$barSet.=$cSetArray[$this->barNumber[$i]];
			}
		}
		$barSet.=$endSet;
		return $barSet;
	}
}
