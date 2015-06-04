<?php
class Printing extends FPDF {
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
