<?php
/**
 *	библиотека универсальных классов	
 * 		
 */

if ( isset($_SESSION['access']['user_id'])  && $_SESSION['access']['user_id'] == 42) {
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
}
	/**
	 *	галлерея изображений для некаталожной продукции
	 *  для КП
	 * 		
	 *	@author  	Алексей Капитонов
	 *	@version 	07.12.2015 13:45
	 */
class rtKpGallery extends aplStdAJAXMethod{
    // для перевода всех приложений в режим разработки раскоментировать и установить FALSE
    protected $production       = false;

    private $user_access        = 0;
    private $user_id            = 0;

    # разрешённые к загрузке типы файлов
    private $fileTypesEnabled   = ['jpg', 'jpeg', 'gif', 'png'];

    /**
     * @return array
     */
    public function getFileTypesEnabled()
    {
        return $this->fileTypesEnabled;
    }


    function __construct(){
        # подключаемся к базе
        $this->db();

        $this->setUserId(isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0);

        # получаем права
        if ($this->getUserId() > 0){
            $this->setUserAccess( $this->get_user_access_Database_Int( $this->getUserId() ) );
        }

        // calls ajax methods from POST
        if(isset($_POST['AJAX'])){
            $this->_AJAX_($_POST['AJAX']);
            $this->responseClass->response['data']['access'] = $this->getUserAccess();
            $this->responseClass->response['data']['id'] = $this->getUserId();
        }

        // calls ajax methods from GET
        if(isset($_GET['AJAX'])){
            $this->_AJAX_($_GET['AJAX']);
            $this->responseClass->response['data']['access'] = $this->getUserAccess();
            $this->responseClass->response['data']['id'] = $this->getUserId();
        }
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = (int)$user_id;
    }

    /**
     * @return int
     */
    public function getUserAccess()
    {
        return $this->user_access;
    }

    /**
     * @param int $user_access
     */
    public function setUserAccess($user_access)
    {
        $this->user_access = (int)$user_access;
    }

    /**
     * запрос прав пользователя
     *
     * @param $id
     * @return int
     */
    public function get_user_access_Database_Int($id){
        $query = "SELECT * FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
        $result = $this->mysqli->query($query) or die($this->mysqli->error);
        $int = 0;
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $int = (int)$row['access'];
                $this->user = $row;
            }
        }
        return $int;
    }

    /**
     * возвращает no_image
     *
     * @param $art
     * @return array
     */
    private function getImagesForArtDefault(){
        $returnImgArr = [];

        # глобальный путь к изображению в сети WWW
        $globalLinkDir = 'http://'.$_SERVER['HTTP_HOST'].'/img/';
        $localLinkDir = DOCUMENT_ROOT.'/../img/';

        # определяем изображение по умолчанию, на случай, если изображений для артикула нет
        # пишем в массив изображение сигнализирующее, что изображения отсутствуют (no_image)
        $returnImgArr[0]['img_name']        = 'no_image.jpg';
        $returnImgArr[0]['img_folder']      = 'img';
        $returnImgArr[0]['img_link_global'] = $globalLinkDir.'no_image.jpg';
        $returnImgArr[0]['img_link_local']  = $localLinkDir.'no_image.jpg';
        $returnImgArr[0]['checked']         = 1;

        return $returnImgArr;
    }

    /**
     * возвращает локальный путь (в файловой системе) до изображеня
     * полученного от поставщика
     *
     * @param $img
     * @return string
     */
    private function getLocalLinkCatalogImg($img){
        return DOCUMENT_ROOT.'/../img/'.$img;
    }

    /**
     * возвращает глобальный путь (ссылку) до изображеня
     * полученного от поставщика
     *
     * @param $img
     * @return string
     */
    private function getGlobalLinkCatalogImg($img){
        return 'http://'.$_SERVER['HTTP_HOST'].'/img/'.$img;
    }

    /**
     * получает изображения загруженные с сатов поставщиков
     * по названию артикула
     *
     * @param $art - string
     * @return array
     */
    private function getImagesForArt($art){
        # объявляем массив изображений
        $imgArr = [];

        # запрашиваем изображения
        if(trim($art) != ''){
            $query = "SELECT*FROM `".IMAGES_TBL."` WHERE `size` = 'big' AND art='".$art."' ORDER BY id";
            $result = $this->mysqli->query($query) or die($this->mysqli->error);
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $imgArr[] = $row['name'];
                }
            }
        }

        # создаем массив, который будем возвращать
        $returnImgArr = [];

        # если в базе найдены изображения для артикула
        if(count($imgArr) > 0){

            # перебор всех найденных изображений
            $i = 0;
            foreach ($imgArr as $imgName) {
                # если файл существует
                if ( file_exists( $this->getLocalLinkCatalogImg( $imgName ) ) ){
                    $returnImgArr[$i]['img_name']           = $imgName;
                    $returnImgArr[$i]['img_folder']         = 'img';
                    $returnImgArr[$i]['img_link_global']    = $this->getGlobalLinkCatalogImg( $imgName );
                    $returnImgArr[$i]['img_link_local']     = $this->getLocalLinkCatalogImg( $imgName );
                    $returnImgArr[$i++]['checked']          = 0;
                }
            }
        }
        # возвращаем массив изображений для артикула
        return $returnImgArr;
    }

    /**
     * возвращает локальный путь (в системе) на папку для загрузки изображений
     *
     * @param $folder
     * @return string
     */
    private function getLocalLinkGalleryUploadDir($folder){
        return DOCUMENT_ROOT.'/data/images/'.$folder.'/';
    }

    /**
     * возвращает глобальный путь (ссылку) на папку для загрузки изображений
     *
     * @param $folder
     * @return string
     */
    private function getGlobalLinkGalleryUploadDir($folder){
        return 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$folder.'/';
    }

    /**
     * сканирует папку и
     * возвращает все файлы и папки загруженные в указанную папку
     *
     * в данную папку, программно реализована только загрузка изображений
     * поэтому проверка на типы фалов не осущевствляется
     *
     * Режим разработки:
     * если папка не найдена будет выброшена ошибка
     *
     *
     * @param string $folder
     * @return array
     */
    private function getImagesFromGallery($i_folder = ''){
        $folder = $i_folder;
        if (trim($folder) == ''){
            return [];
        }

        if($folder != '' ){
            # определяем локальный путь
            $localLinkGalleryUploadDir = $this->getLocalLinkGalleryUploadDir( $folder );

            # показываем ошибки в режиме разработки
            if (!is_dir( $localLinkGalleryUploadDir )){
                $this->prod__window( "Произошла непредвиденная ошибка,<br> папка <b>" . $localLinkGalleryUploadDir . "</b> не найдена в фаловой системе" );
                return [];
            }

            # сканируем директории.
            $files = scandir( $localLinkGalleryUploadDir );

            # счётчик
            $j = 0;

            # получаем глобальный путь
            $globalLinkGalleryUploadDir = $this->getGlobalLinkGalleryUploadDir( $folder );

            # создаём массив, который будем возвращаать
            $returnImgArr = [];

            # перебираем содержимое директории
            for ($i = 0; $i < count($files); $i++) { # Перебираем все файлы
                if (($files[$i] == ".") || ($files[$i] == "..")) { # Текущий каталог и родительский пропускаем
                    continue;
                }
                if (file_exists( $localLinkGalleryUploadDir . $files[$i] )) {
                    # если файл существует
                    $returnImgArr[$j]['img_name']           = $files[$i];
                    $returnImgArr[$j]['img_folder']         = $folder;
                    $returnImgArr[$j]['img_link_global']    = $globalLinkGalleryUploadDir . $files[$i];
                    $returnImgArr[$j]['img_link_local']     = $localLinkGalleryUploadDir . $files[$i];
                    $returnImgArr[$j++]['checked']          = 0;
                }
            }
        }
        return $returnImgArr;
    }

    /**
     * получаем информацию по изображениям для позиции
     *
     * данный метод в любом случае выдаёт хотя бы одно выбранное изображение
     *
     * в случае отсутствия выбранных изображений метод выберет первое из каталожных
     * в случае отсутствия выбранных И отсутсвия каталожных - вернёт первое из загруженных
     * в случае отсутствия выбранных И отсутсвия каталожных И отсутствия загруженных - вернёт no_image
     *
     *
     * @param $rt_main_row
     * @return string
     */
    private function getImagesForPosition( $rt_main_row ){
        # получаем изображения пришедшие к нам от поставщика и показанные на сайте
        $imgArrForArt = $this->getImagesForArt( $rt_main_row['art'] );

        # получаем изображения загруженные к нам локальн, через окно галлереи
        $folder = $rt_main_row['img_folder'];
        $imgArrFromGallery = $this->getImagesFromGallery( $folder,$rt_main_row['id'] );



        # если не было найдено изображений - возвращаем дефолтное no_image
        if (count( $imgArrForArt ) == 0 && count( $imgArrFromGallery ) == 0){
            # получаем дефолное изображение
            return [];
        }

        # на данном этапе мы имеем 2 массива изображений и минимум одно изображение в одном из них
        # все содержащиеся изображения проверены на их наличие в файловой системе

        # получаем объединённый массив изображений
        $returnArr = array_merge( $imgArrForArt, $imgArrFromGallery );

        # получаем массив отмеченных изображений
        $checkedArr = $this->getCheckedImg( $rt_main_row['id'] );

        # отмечаем изображения являющиеся выбранными или выбранными по умолчанию
        $returnArr = $this->checkChooseImages( $returnArr, $checkedArr );

        return $returnArr;
    }

    /**
     * помечает выбранные изображения как отмеченные
     *
     * @param $imagesArr
     * @param $checkedArr
     * @return array
     */
    private function checkChooseImages($imagesArr = [], $checkedArr = []){
        $startArr           = [];
        $chosenNum          = 0;
        $endArr             = [];
        $checkedImgNamesArr = [];

//        echo $this->printArr($checkedArr);

        foreach ($checkedArr as $row){
            $checkedImgNamesArr[] = $row['img_name'];
        }

        # сверяем массив изображений с массивом выбранных изображений
        foreach ($imagesArr as $key => $imgArr){
            # помечаем соответствия
            if ( in_array($imgArr['img_name'], $checkedImgNamesArr) ){
                $startArr[ $chosenNum ] = $imgArr;
                $startArr[ $chosenNum++ ]['checked'] = 1;
            }else{
                $endArr[] = $imgArr;
            }
        }

        # сортируем выбранные изображения
        $startSortArr = [];
        foreach ($checkedImgNamesArr as $imgChooseName){
            foreach ($startArr as $row){
                if($row['img_name'] == $imgChooseName){
                    $startSortArr[] = $row;
                }
            }
        }


        # объединяем массивы выбранных и невыбранных изображений вместе
        # так, чтобы выбранные были первыми
        $i = 0;
        foreach ($startSortArr as $row){
            $returnArr[$i++] = $row;
        }
        foreach ($endArr as $row){
            $returnArr[$i++] = $row;
        }
//         = array_merge( $startSortArr, $endArr );

        # если не выбрано ниодного изображения - выбираем первое одно, оно и будет выдаваться по умолчанию
        if (count( $returnArr ) > 0 && $chosenNum == 0){
            $returnArr[ 0 ]['checked'] = 1;
        }

        return $returnArr;
    }

    /**
     * создание новой папки
     *
     * @param $rt_main_row_id
     * @return string
     */
    protected function createNewDir($rtMainRowId){
        # генерим название название для новой папки папки
        $folderName = $this->generateNameForNewGalleryFolder();

        # получаем локальный путь к папке
        $dirName = $this->getLocalLinkGalleryUploadDir($folderName);

        // если папка $dirName не существует
        if (!is_dir($dirName)) {
            # создание директории
            mkdir($dirName, 0777, true);

            # пишем её название в базу
            $query = "UPDATE `".RT_MAIN_ROWS."` SET";
            $query .=" img_folder = '".$folderName."'";
            $query .=" WHERE `id` = ".(int)$rtMainRowId.";";
            $result = $this->mysqli->query($query) or die($this->mysqli->error);
        }

        return $folderName;
    }

    /**
     * возвращает название для новой папки
     * метод гарантирует название, которого ещё нет
     * @return string
     */
    private function generateNameForNewGalleryFolder(){
        return md5(time());
    }


    /**
     * возвращает названия изображений
     * хранящиеся в базе как выбранные
     *
     * @param $rt_main_row_id
     * @return array
     */
    protected function getCheckedImg( $rtMainRowId ){

        $query = "SELECT * FROM `".RT_MAIN_ROWS_GALLERY."` WHERE `parent_id` = '".(int)$rtMainRowId."' order by sort ASC";
        $result = $this->mysqli->query($query) or die($this->mysqli->error);

        $arr = [];
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $arr[] = $row;
            }
        }
        return $arr;
    }

    /**
     * собирает и возвращает нформацию для формирования окна галлереи
     */
    protected function get_gallery_content_AJAX(){
        if(!isset($_POST['id'])){
            $html = 'Отсутствует id.';
            $this->responseClass->addMessage($html,'error_message');
            return;
        }

        $this->getGalleryContent( (int)$_POST['id'] );
    }

    /**
     * собирает и возвращает в AJAX ответ иформацию для формирования окна галлереи
     *
     * @param $rtMainRowId - int
     */
    private function getGalleryContent( $rtMainRowId ){
        # получаем данные по позиции
        $positionArr    = $this->getPosition( $rtMainRowId );
        # определяем переменную содержащую имя папки для последующих загрузок
        $folderName     = $positionArr['img_folder'];

        # если папка указана, но её по каким-то причинам не существует - создаём её
        if($folderName != '' && !is_dir( $this->getLocalLinkGalleryUploadDir( $folderName ) ) ) {
            # создание новой папки
            $folderName = $this->createNewDir( $rtMainRowId );
            $positionArr['img_folder'] = $folderName;
        }

        $this->responseClass->response['data']['id']            = $rtMainRowId;
        $this->responseClass->response['data']['timestamp']     = $this->getTimestamp();
        $this->responseClass->response['data']['token']         = $this->getToken( $this->getTimestamp() );
        $this->responseClass->response['data']['folder_name']   = $folderName;
        $this->responseClass->response['data']['images']        = $this->getImagesForPosition( $positionArr );

        # всегда отправляем данные по картинке с отсутствующим изображением
        $this->responseClass->response['data']['no_images']     = $this->getImagesForArtDefault();
    }

    /**
     * генератор индивидуальной метки
     *
     * @param $timestamp
     * @return string
     */
    private function getToken($timestamp){
        return md5('unique_salt' . $timestamp);
    }

    /**
     * @return int
     *
     */
    private function getTimestamp(){
        if (!isset( $this->timestump )){
            $this->timestump = time();
        }
        return $this->timestump;
    }


    /**
     * запрос на загрузку новых изображений
     */
    protected function add_new_files_in_kp_gallery_AJAX(){
        $firstImg = false;

        # принимаем данные
        $folderName     = isset($_POST['folder_name'])?$_POST['folder_name']:'';
        $rtMainRowId    = (int)$_POST['id'];
        $timeStump      = (int)$_POST['timestamp'];
        $token      = $_POST['token'];

        # получаем локальный путь к папке
        $localLinkGalleryUploadDir = $this->getLocalLinkGalleryUploadDir( $folderName );

        # если папка ещё не была указана
        # ИЛИ она указана, но её по каким-то причинам не существует - создаём её
        if(!isset($folderName) || $folderName == "" || !is_dir( $localLinkGalleryUploadDir ) ) {
            # создание новой папки
            $folderName = $this->createNewDir( $rtMainRowId );
            $localLinkGalleryUploadDir  = $this->getLocalLinkGalleryUploadDir( $folderName );
        }

        # возвращаем назад название папки (обновим данные в скрипте на вский случай)
        $this->responseClass->response['data']['folder_name']   = $localLinkGalleryUploadDir;

        # если файл был прислан
        if ($_FILES && !empty($_FILES)) {
            # подсчитываем проверочный token
            $verifyToken = $this->getToken( $timeStump );

            # если маркер оказался валидным
            if ($token == $verifyToken) {

                # получаем данные по файлу в переменную
                $tempFile   = $_FILES['Filedata']['tmp_name'];

                # получаем расширение файла
                $fileParts  = pathinfo($_FILES['Filedata']['name']);
                $extension  = strtolower($fileParts['extension']);

                # сверяем расширение файла с разрешённым для закачки списком расширений
                if ( in_array($extension, $this->getFileTypesEnabled()) ) {

                    # получаем уникальное имя файла
                    $fileNameExtension = $this->createNewFileName( $extension, $verifyToken, $localLinkGalleryUploadDir );

                    # локальный путь к файлу
                    $targetFile = $localLinkGalleryUploadDir . $fileNameExtension;

                    # сохраняем файл
                    move_uploaded_file($tempFile, $targetFile);

                    $this->responseClass->addMessage('Изображение успешно загружено','successful_message',100);

                    # получаем глобальный путь к изображению
                    $globalLinkGalleryUploadDir = $this->getGlobalLinkGalleryUploadDir( $folderName );

                    # собираем данные по загруженному изображению
                    $returnImgArr[0]['img_name']           = $fileNameExtension;
                    $returnImgArr[0]['img_folder']         = $folderName;
                    $returnImgArr[0]['img_link_global']    = $globalLinkGalleryUploadDir . $fileNameExtension;
                    $returnImgArr[0]['img_link_local']     = $localLinkGalleryUploadDir . $fileNameExtension;
                    $returnImgArr[0]['checked']          = 0;

                    $this->responseClass->response['data']['images'] = $returnImgArr;

                } else {

                    // загрузка была отклонена
                    $mess = 'Данный формат файла не разрешён системой';
                    $mess .= '<br>';
                    $mess .= 'разрешены следующие форматы: ' . implode(",", $this->getFileTypesEnabled() );
                    $this->responseClass->addMessage($mess, 'error_message');
                }
            }
        }
    }

    /**
     * создает имя фала
     *
     * # при мультизагрузке очень важно, чтобы файлам раздавались свободные имена !!!
     *
     * !!! если при мультизагрузке начнут теряться файлы, то это прямой показатель,
     * что необходимо писать проверку на свободное имя файла в файловой системе
     * т.к. при тестировании проблем не выявлено был оставлен текущий алгоритм без проверки, он быстрее
     *
     * @param $extension    string  -
     * @param $verifyToken  string  -  метка обращения
     * @return string
     */
    private function createNewFileName( $extension, $verifyToken, $path ){
        # $randomPart - добавлен для того, чтобы файлы не перетирали друг друга при мультизагрузке
        $randomPart = rand(5, 15);

        # сборка имени файла
        $fileName = md5(mktime(date("H"), date("i"), date("s"), date("n"), date("j"), date("Y")).$randomPart)."_".$verifyToken;
        # добавляем расширение файла
        $fileNameExtension = $fileName . ".$extension";

        # если файл существует - генерируем имя по новой
        if (file_exists( $path . $fileNameExtension )){
            $fileNameExtension = createNewFileName( $extension, $verifyToken, $path );
        }

        return $fileNameExtension;
    }

    /**
     * возвращает строку позиции из базы или []
     *
     * @param $id
     * @return array
     */
    private function getPosition($id){
        // запрос наличия выбранного изображения для данной строки
        $query = "SELECT * FROM `".RT_MAIN_ROWS."` WHERE `id` = '".(int)$id."' ";

        $result = $this->mysqli->query($query) or die($this->mysqli->error);

        if($result->num_rows > 0){
            // echo $result->num_rows;
            while($row = $result->fetch_assoc()){
                return $row;
            }
        }
        return [];
    }

    /**
     * запрос на обновление информации по выбранным изображениям
     */
    protected function save_edit_gallery_AJAX(){
        if (!isset($_POST['mainRowId']) || (int)$_POST['mainRowId'] == 0){
            $this->prod__message("Не получен ID",'error_message');
            return;
        }
        if (!isset($_POST['chooseData']) || count($_POST['chooseData']) == 0){
            $this->prod__message("Не получены данные для сохранения",'error_message');
            return;
        }

        #обновление информации по выбранным изображениям
        $this->saveEditGallery((int)$_POST['mainRowId'], $_POST['chooseData']);
    }

    /**
     * обновление информации по выбранным изображениям
     */
    private function saveEditGallery($id, $newData = [] ){
        # удаление старых данных
        $query = "DELETE FROM `".RT_MAIN_ROWS_GALLERY."` WHERE `parent_id` =?";

        $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
        $stmt->bind_param('i', $id) or die($this->mysqli->error);
        $stmt->execute() or die($this->mysqli->error);
        $result = $stmt->get_result();

        foreach ($newData as $key => $data){
            $query = "INSERT INTO `".RT_MAIN_ROWS_GALLERY."` SET ";
            $query .= "  `sort` =?";
            $query .= ", `folder` =?";
            $query .= ", `img_name` =?";
            $query .= ", `parent_id` =?";

            $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
            $stmt->bind_param('issi', $key, $data['img_folder'], $data['img_name'], $id) or die($this->mysqli->error);
            $stmt->execute() or die($this->mysqli->error);
            $result = $stmt->get_result();


        }
        $stmt->close();
    }

    /**
     * запрос на удаление изображения
     */
    protected function delete_upload_image_AJAX(){
        if (!isset($_POST['folder_name']) || trim($_POST['folder_name']) == ''){
            $this->prod__message("Не получено имя папки",'error_message');
            return;
        }

        if (!isset($_POST['img_name']) || trim($_POST['img_name']) == ''){
            $this->prod__message("Не получено название изображения",'error_message');
            return;
        }
        if ($this->deleteUploadImage($_POST['folder_name'], $_POST['img_name'])){
            $this->responseClass->addMessage("Изображение удалено",'successful_message');
        }
    }

    /**
     * удаление изображения
     *
     * @param $folder
     * @param $file
     * @return bool
     */
    private function deleteUploadImage( $folder, $file ){

        $fileName = $file;
        $uploadLocalDir = $this->getLocalLinkGalleryUploadDir( $folder );

        $filename = $uploadLocalDir.$fileName;

        // если файл существует
        if (file_exists($filename)) {
            // удаляем файл
            unlink($filename);
            return true;
        }
        return false;
    }




    ///////////////
    # СТАРЬЁ
    ////////////////





 		//////////////////////////
		//	KP
		//////////////////////////
 			// сохранение главного изображения
 			protected function chooseImgGallery_AJAX(){
	 			//////////////////////////
	 			//	предупреждения для юзера
	 			//////////////////////////
	 				// если не получено название папки
	 				if(!isset($_POST['data']['id'])){
	 					$html = 'ID не указан';	
						$this->responseClass->addMessage($html,'error_message');
	 					return;
	 				}
	 				// если не получено название изоюбражения
	 				if(!isset($_POST['data']['img'])){
	 					$html = 'Image не указана';	
						$this->responseClass->addMessage($html,'error_message');
	 					return;
	 				}
	 				if(!isset($_POST['data']['type'])){
	 					$html = '<br>Не указан тип изображения';	
						$this->responseClass->addMessage($html,'error_message');
	 					return;
	 				}

	 			// удаление загруженных файлов
	 			if(isset($_POST['data']['delete_img']) && trim($_POST['data']['delete_img']) != ''){
	 				// 	$html = 'Присутствуют изображения на удаление';	
					// $this->responseClass->addMessage($html,'error_message');
	 				$img_arr = explode(",", $_POST['data']['delete_img']);
	 				foreach ($img_arr as $key => $value) {
	 					$dir = ROOT.'/data/images/'.$_POST['data']['delete_img_width_folder'].'/';
	 					// полный путь к файлу 
	 					$filename = $dir.''.$value;
	 					
	 					// если файл существует
						if (file_exists($filename)) {
							// удаляем файл
							   unlink($filename);
						}	 					
	 				}
					
					// если папка пуста - удаляем её
					// if(rmdir($dir)){
					// 	if($this->user_access){
					// 		$html = 'Т.к. изображений во временной папке не осталось, папка была удалена.';	
					// 		$this->responseClass->addMessage($html,'error_message');	
					// 	}	
						// // на всякий случай сохраняем папку
						// if($_POST['data']['folder_name'] != 'img' && trim($_POST['data']['folder_name']) != ''){
						// 	$_POST['data']['folder_name'] = 'img';					
						// }	
						// $_POST['data']['folder_name'] = 'img';					
					// }
	 			} 				

	 			// вычищаем предыдущие данные из базы
	 			if(trim($_POST['data']['json']) != trim($_POST['data']['json_old'])){
		 			$query = "DELETE FROM `".RT_MAIN_ROWS_GALLERY."` WHERE parent_id = ".(int)$_POST['data']['id'].";";
		 			$result = $this->mysqli->query($query) or die($this->mysqli->error);

		 			$json = json_decode($_POST['data']['json'], true);	 			
		 							
		 			
		 			foreach ($json as $key => $IMG) {
		 				$query = "INSERT INTO `".RT_MAIN_ROWS_GALLERY."` SET ";
		 				$query .= "`sort` = '".$key."'";
		 				$query .= ", `folder` = '".$IMG['folder']."'";
		 				$query .= ", `img_name` = '".$IMG['img_name']."'";
		 				$query .= ", `parent_id` = '".$_POST['data']['id']."'";
		 				$result = $this->mysqli->query($query) or die($this->mysqli->error);
		 			}
		 		}
	 			
				// $query .=" img_folder = '".$dir."'";
				// $query .=", img_folder_choosen_img = '".$img."'";
				// $result = $mysqli->query($query) or die($mysqli->error);


 				// сохраняем значение в базе
 				if( $_POST['data']['folder_name'] != 'img' ){
					$query = "UPDATE `".RT_MAIN_ROWS."` SET";
					// $query .=" img_folder_choosen_img = '".$_POST['data']['img']."'";
				
				
					$query .=" img_folder = '".$_POST['data']['folder_name']."'";	
					

					// $query .=", img_type = '".$_POST['data']['type']."'";	
					$query .=" WHERE `id` = ".(int)$_POST['data']['id'].";";
					
					$result = $this->mysqli->query($query) or die($this->mysqli->error);
				}	
				$html = 'OK';	
				$this->responseClass->addMessage($html,'successful_message','25000');
				// $this->responseClass->addResponseFunction('window_reload');
				return;
 			}






            /**
             * собирает json выбранных изображений
             *
             */
 			private function getJsonCheckedImg( $chooseImages ){
 				$json = '[';

 				$json = "[";
 				foreach ($chooseImages as $key => $value) {
 					$json .= (($key > 0)?',':'').'{"folder":"'.$value['folder'].'","img_name":"'.$value['img_name'].'"}';
 				}
 				$json .= "]";
 				return $json;
 			}






			//////////////////////////
			//	из common.php
			//////////////////////////
				protected function transform_img_size($img,$limit_height,$limit_width){
			     	list($img_width, $img_height, $type, $attr) = (file_exists($img))? getimagesize($img): array($limit_width,$limit_height,'',''); 
					$limit_relate = $limit_height/$limit_width;
					$img_relate = $img_height/$img_width;
					if($limit_relate < $img_relate) $limit_width = $limit_height/$img_relate; 
					else $limit_height = $limit_width*$img_relate;
					return array($limit_height,$limit_width); 
				}
				protected function get_big_img_name($art){
			        global $db;				   
			        $query = "SELECT*FROM `".IMAGES_TBL."` WHERE `size` = 'big' AND art='".$art."' ORDER BY id";
				    $result = mysql_query($query,$db);
				    if($result && mysql_num_rows($result)>0){
					   $row = mysql_fetch_assoc($result);
				       $img = ($row['name'] !='')? $row['name']:'no_image.jpg';
				    }
				    else $img = 'no_image.jpg';
				    return $img;	
			    }
			    protected function checkImgExists($path,$no_image_name = NULL ){
				    $mime = $this->getExtension($path);
					if(@fopen($path, 'r')){//file_exists
						$img_src = $path;	
					}
					else{
					    $no_image_name =!empty($no_image_name)? $no_image_name :'no_image';
						$img_src= substr($path,0,strrpos($path,'/') + 1).$no_image_name.'.'.$mime;
					} 
					return $img_src;
				}
				protected function getExtension($filename){
			        $path_info = pathinfo($filename);
			        return $path_info['extension'];
			    }

			// получаем изображение для артикула
			private function getArtImg($item){
				$img_path = '../img/'.$this->get_big_img_name($item['art']);	
			    $img_src = $this->checkImgExists($img_path);
				// меняем размер изображения
			    $size_arr = $this->transform_img_size($img_src,226,300); // установленое здесь значение для высоты является оптимальным 
				                                                   // при выводе КП на печать (не съезжают ячейки таблицы)
				// вставляем изображение
				return '<img src="'.$img_src.'" height="'.$size_arr[0].'" width='.$img_src[1].'">';
			}

			// получаем html изображения
			private function getImgLiHtml($path, $file = '',$li_class = '', $folder = '', $type){
				$html = '<li class="rt-gallery-cont '. $li_class .'" data-type="'.$type.'" data-folder="'.$folder.'" data-file="'.$file.'" >';
				if($folder != 'img'){
					$html .= '<div class="delete_upload_img">x</div>';	
				}
				$html .= '<img src="'.$path.'" alt="" />'; // Вывод превью картинки
				$html .= '</li>';
				return $html;
			}







			

}
		
	
?>