<?php

	// requirement 03
trait Upholstery {
    protected $upholstery;
    public function getUpholstery() {
        return $this->upholstery;
    }
}
 
// requirement 04
trait MaxSquare {
    protected $maxSquare = 15;
    public function getMaxSquare() {
        return $this->maxSquare;
    }
}

// echo;
abstract class Documetnt {
    protected $autor = 12;
    private $user;
 
    // requirement 02
    protected $color;
    protected $material;
    public function getColor() {
        return $this->color;
    }
    public function getMaterial() {
        return $this->material;
    }
}


/**
 *	счёт
 *
 *	@author  	Alexey Kapitonov
 *	@version 	14:43 01.04.2016
 */
class Bill extends Documetnt {
    // requirement 04
    use MaxSquare;
 	
 	protected $name = 'счет';
    protected $number;
    protected $dateCreate;
    public function getNumber() {
        return $this->number;
    }
}

/**
 *	счет оферта
 *
*	@author  	Alexey Kapitonov
 *	@version 	14:43 01.04.2016
 */
class Invoce extends Documetnt {
    // requirement 03
    use Upholstery;
 	
 	protected $name = 'счет - оферта';
    protected $number;
    protected $dateCreate;
    public function getNumber() {
        return $this->number;
    }
}
	// варианты просчётов
	ob_start();	
	$f = new Invoce();	
		echo '<pre>';
		print_r($f);
		echo '</pre>';
			
		$content = ob_get_contents();
	ob_get_clean();

	











?>