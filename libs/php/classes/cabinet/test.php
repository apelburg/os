<?php
error_reporting('E_ALL');
echo 'asd';


interface iTranslateException{
    /**
     * @return mixed
     */
    public function getTranslateVars();
}

class translateException extends baseException implements iTranslateException{
    /**
     * @var mixed
     */
    protected $translateVars = null;

    /**
     * @param string $message
     * @param mixed $variables
     * @param Exception $previous
     */
    public function  __construct($message = '', $variables = null, Exception $previous = null) {
        echo 'hellow world';
        parent::__construct($message, 0, $previous);
        $this->translateVars = $variables;
    }

    /**
     * @return mixed
     */
    public function getTranslateVars(){
        return $this->translateVars;
    }
}

class translate{
    const localeRu = 1;
    const localeDe = 2;
    public static $translateRu = array(
        'Number:%d' => 'Номер:%d',
        'Error'     => 'Ошибка'
    );
    public static $translateDe = array(
        'Number:%d' => 'Zahl:%d',
        'Error'     => 'Fehler'
        );
    public static $currentLocale;

    public static function getTranslateString($s){
        $st = '';
        switch( self::$currentLocale ){
            case self::localeRu: $st = self::$translateRu[$s];
                break;
            case self::localeDe: $st = self::$translateDe[$s];
                break;
        }
        if(!strlen($st)){
            $st = $s;
        }
        return $st;
    }

    /**
     * Метод которыи используется во view, для отображения ошибок
     *
     * @param translateException $e
     * @return string
     */
    public static function viewErrorDisplay($e){
        if(!($e instanceof iTranslateException)){
            throw new baseException('Only for iTranslateException', 0, $e);
        }
        $st = self::getTranslateString( $e->getMessage() );
        $tv = $e->getTranslateVars();
        if( empty($tv) ){
            return $st;
        }
        if(!is_array($tv)){
            $tv = array($st, $tv);
        } else{
            array_unshift($tv, $st);
        }
        return call_user_func_array('sprintf', $tv);
    }

}

translate::$currentLocale = translate::localeDe;

try {
    throw new translateException('Number:%d', 1);
    throw new translateException('Number:%d, String:%s', array(1, 'abc'));
    throw new translateException('Error');
} catch (translateException $e) {
    die('error',$e);
}