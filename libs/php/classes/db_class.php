<?php
class Result
{
  //Переменные которые потребуются для работы
    private $stmt = null;
    private $res = null;
    private $result = array();
    private $data = array();
    private $params = array();
   //Конструктор в который я передаю интересные параметры
//$mysqli = new mysqli("localhost", "root", "", "blogs");
    //if ($mysqli->connect_errno) {
        //echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli-//>connect_error;
    //}
// $mysqli – это текущее соединение с бд ($mysqli = new mysqli("localhost", "root", "", "blogs");)
//$arr – массив где содержатся 
//1. Обязательно тип переменных ‘i(int), s(string), b(blob), d(double)’
//2. Подставляемы переменные вместо ? в запросе
//$arrColumn – массив ключей, которые мы хотим стырить ‘id’, ‘login’ и т.д.
//$query – сам запрос в базу данных
    public function __construct($mysqli, $arr, $arrColumn, $query)
    {
//Инициализируем STMT
        $this->stmt = $mysqli->stmt_init();

//1.Подготавливаем запрос
//2. Делаем привязку по параметрам
//3. Выдавливаем  полученные значения из бд
//4. Самое интересное, что меня ввело в стопр, привязка переменных( грубо говоря одной строки )
//Которые получатся как $this->stmt->fetch() 
//К массиву по ссылке
//5. Делаем буферизованный запрос
//6. Закрываем соединение
//7. Высвобождаем память
        try {
            $this->prepare($query);
            $this->bind($arr);
            $this->execute();
            $this->attachResult($arrColumn);
            $this->store();
            $this->fetch($arrColumn);
            $this->close();
            $this->free();
        } catch (Exception $e) {
            Debug.log('Select Error (' . $this->stmt->errno . ') ' . $this->stmt->error);
        }
    }
    // подготовливаем запрос, там куда будут вствлятся данные отмечаем символом ? (плейсхолдоры)
    private function prepare($query)
    {
        $this->stmt->prepare($query);
    }
    // привязываем переменные к плейсхолдорам
    //i (int), d (double), s (string), b (blob)
    private function bind($arr)
    {
//Вызываем кэллбэк для bind_param чтобы передать туда массив
        call_user_func_array(array($this->stmt, "bind_param"), $this->refValues($arr)) ;
    }
    //подгоняем массив, чтобы можно было его засунуть параметром в функцию
//Клёвая функция!
    public function refValues($arr){
        if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
        {
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }
    // отправляем данные, которые на данный момент находятся в привязанных переменных
    private function execute()
    {
        $this->stmt->execute();
    }
    // привязываем переменную для получения в нее результата
    private function attachResult($arrColumn)
    {
        /* // В дальнейшем использовать для модификации,
           // чтобы не вводить поля, в которые сохранятся
            # of fields in result set.
            $nof = mysqli_num_fields( mysqli_stmt_result_metadata($handle) );
            # The metadata of all fields
            $fieldMeta = mysqli_fetch_fields( mysqli_stmt_result_metadata($handle) );
            # convert it to a normal array just containing the field names
            $fields = array();
            for($i=0; $i < $nof; $i++)
                $fields[$i] = $fieldMeta[$i]->name;
        */
        //создаём массив ключ (набор своих колонок) => значение (выбранные значения из БД)
        foreach($arrColumn as $col_name)
        {
            // Assign the fetched value to the variable '$data[$name]'
            $this->params[$col_name] = &$this->data[$col_name] ;
        }
        //вызываем callback и передаём туда массив
//Не забываем, что этот массив $this->params привязан по адресу, он будет меняться
        $this->res = call_user_func_array(array($this->stmt, "bind_result"),  $this->params);

    }
    // делаем запрос буферизированным,
    // если бы этой строки не было, запрос был бы небуферезированым
    private function store()
    {
        $this->stmt->store_result();
    }
    // получение результата из привязанной переменной
    public function fetch($arrColumn)
    {
//Волшебная строка, с помощью которой можно выдернуть значение по ссылке на всегда,
//даже если, значение по ссылке изменится
//Собираем результатирующий массив
        $copy = create_function('$a', 'return $a;');
        if($this->res)
        {
            while($this->stmt->fetch()){
                $this->result[] = array_combine($arrColumn, array_map($copy, $this->params) );
            }
        }
    }
    private function close()
    {
        $this->stmt->close();
    }
    private function  free()
    {
        $this->stmt->free_result();
    }
    public function getResult()
    {
        return $this->result;
    }
    public function getJSON()
    {
        return json_encode($this->result);
    }
}
?>