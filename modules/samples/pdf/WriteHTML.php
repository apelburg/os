<?php
require('fpdf.php');

class PDF_HTML extends FPDF
{
	var $B=0;
	var $I=0;
	var $U=0;
	var $HREF='';
	var $ALIGN='';

	function WriteHTML($html)
	{
		//HTML parser
		$html=str_replace("\n",' ',$html);
		$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		foreach($a as $i=>$e)
		{
			if($i%2==0)
			{
				//Text
				if($this->HREF)
					$this->PutLink($this->HREF,$e);
				elseif($this->ALIGN=='center')
					$this->Cell(0,5,$e,0,1,'C');
				else
					$this->Write(5,$e);
			}
			else
			{
				//Tag
				if($e[0]=='/')
					$this->CloseTag(strtoupper(substr($e,1)));
				else
				{
					//Extract properties
					$a2=explode(' ',$e);
					$tag=strtoupper(array_shift($a2));
					$prop=array();
					foreach($a2 as $v)
					{
						if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
							$prop[strtoupper($a3[1])]=$a3[2];
					}
					$this->OpenTag($tag,$prop);
				}
			}
		}
	}

	function OpenTag($tag,$prop)
	{
		//Opening tag
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($tag,true);
		if($tag=='A')
			$this->HREF=$prop['HREF'];
		if($tag=='BR')
			$this->Ln(5);
		if($tag=='P')
			$this->ALIGN=$prop['ALIGN'];
		if($tag=='HR')
		{
			if( !empty($prop['WIDTH']) )
				$Width = $prop['WIDTH'];
			else
				$Width = $this->w - $this->lMargin-$this->rMargin;
			$this->Ln(2);
			$x = $this->GetX();
			$y = $this->GetY();
			$this->SetLineWidth(0.4);
			$this->Line($x,$y,$x+$Width,$y);
			$this->SetLineWidth(0.2);
			$this->Ln(2);
		}
	}

	function CloseTag($tag)
	{
		//Closing tag
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($tag,false);
		if($tag=='A')
			$this->HREF='';
		if($tag=='P')
			$this->ALIGN='';
	}

	function SetStyle($tag,$enable)
	{
		//Modify style and select corresponding font
		$this->$tag+=($enable ? 1 : -1);
		$style='';
		foreach(array('B','I','U') as $s)
			if($this->$s>0)
				$style.=$s;
		$this->SetFont('',$style);
	}

	function PutLink($URL,$txt)
	{
		//Put a hyperlink
		$this->SetTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->Write(5,$txt,$URL);
		$this->SetStyle('U',false);
		$this->SetTextColor(0);
	}
	function Title($title,$image,$company_name,$company_adres,$company_tel,$company_site) {
        $this->Image($image,6,6,30,20);
        $this->Cell(30); // выводим пустую €чейку, ширина которой 30
        $this->SetFont('Arial-BoldMT','',10); // задаем шрифт, и размер шрифта
        $this->Cell(40,4,$company_name,0,0,'L',0); // выводим название компании
        $this->Cell(70);
        $this->SetFillColor(187,189,189);  // задаем цвет заливки следующих €чеек (R,G,B)
        $this->Cell(50,4,$title,0,0,'C',1); // выводим наименование компании
        $this->ln(); // переходим на следующую строку
        $this->Cell(30);
        $this->SetFont('ArialMT','',10);
        $this->Cell(40,4,$company_adres,0,10,'L',0); // выводим адрес компании
        $this->Cell(40,4,$company_tel,0,10,'L',0); // выводим телфон компании
        $this->Cell(40,4,$company_site,0,10,'L',0); // выводим адрес сайта компании
    }
	
	function OutputTable($header,$query) {
        $w=array(10,70,40,30,30,30); // ћассив с шириной столбцов
        $this->Cell(10);
        $this->SetFont('Arial-BoldMT','',11);
        for($i=0;$i<count($header);$i++){$this->Cell($w[$i],7,$header[$i],1,0,'C');}
        $this->Ln();
        $this-> SetFont('ArialMT','',8);
        while($array = mysql_fetch_assoc($query))
             {
              $this->Cell(10);
              $this->Cell(10,4,$array['art'],1,0,'C',0);
              $this->Cell(70,4,$array['name'],1,0,'L',0);
              $this->Cell(40,4,$array['cena'],1,0,'C',0);
              $this->Cell(30,4,$array['opt_cena'],1,0,'C',0);
              $this->Cell(30,4,$array['kol_vo'],1,0,'C',0,1);
              $this->ln();
             }
    }
}
?>
