<?php

/**
 * Class Pagination
 *
 * @author  Alexey Kapitonov
 * @version 15.08.16 11:30
 */
class Pagination{
    # количество записей на странице
    private $rowOnPage = 15;

    # номер текущей страницы
    private $pageNumber = 1;

    # количество строк
    private $numberOfRows = 0;

    # колдичество страниц
    private $numberOfPages = 1;

    # рабочая ссылка
    private $worker_link = '';

    # максимальное количетво ссылок на странице
    private $maxPaginationLinks = 9;

    # номер страницы в первой ссылке в ряду пагинации
    private $paginationLinksPageEnd = 0;

    # номер страницы в последней ссылке в ряду пагинации
    private $paginationLinksPageStart = 0;

    # ключ GET параметра, содержащего номер страницы
    private $pageNumberParameterName = 'page_number';

    /**
     * @return string
     */
    public function getPageNumberParameterName()
    {
        return $this->pageNumberParameterName;
    }

    /**
     * @return int
     */
    public function getPaginationLinksPageEnd()
    {
        return $this->paginationLinksPageEnd;
    }

    /**
     * @param int $paginationLinksPageEnd
     */
    public function setPaginationLinksPageEnd($paginationLinksPageEnd)
    {
        $this->paginationLinksPageEnd = $paginationLinksPageEnd;
    }

    /**
     * @return int
     */
    public function getPaginationLinksPageStart()
    {
        return $this->paginationLinksPageStart;
    }

    /**
     * @param int $paginationLinksPageStart
     */
    public function setPaginationLinksPageStart($paginationLinksPageStart)
    {
        $this->paginationLinksPageStart = $paginationLinksPageStart;
    }

    /**
     * @return int
     */
    public function getMaxPaginationLinks()
    {
        return $this->maxPaginationLinks;
    }

    /**
     * @return int
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * @param int $numberOfPages
     */
    public function setNumberOfPages($numberOfPages)
    {
        $this->numberOfPages = $numberOfPages;
    }

    /**
     * @return int
     */
    public function getNumberOfRows()
    {
        return $this->numberOfRows;
    }

    /**
     * @param $queryForCount
     */
    public function setNumberOfRows($queryForCount)
    {

        $numberOfRows = 0;

        $this->db();
        $result = $this->mysqli->query($queryForCount) or die($this->mysqli->error);

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()) {
                $numberOfRows = (int)$row['count'];
            }
        }

        $this->numberOfRows = $numberOfRows;
    }



    /**
     * @return int
     */
    public function getRowOnPage()
    {
        return $this->rowOnPage;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * @param $i_pageNumber int
     */
    public function setPageNumber($i_pageNumber)
    {
        $o_pageNumber = ($i_pageNumber > 0)?$i_pageNumber:1;
        $this->pageNumber = $o_pageNumber;
    }

    //The single instance
    private static $_instance;



    private function __construct($queryCount){
        # получаем номер страницы, на котором находится пользователь
        if(isset($_GET[ $this->getPageNumberParameterName() ])){
            $this->setPageNumber((int)$_GET[ $this->getPageNumberParameterName() ]);
        }

        # получаем и запоминаем общее количество строк
        $this->setNumberOfRows($queryCount);

        # подсчитываем количество страниц
        $this->setNumberOfPages( $this->calcNumberOfPages() );


    }

    /**
     * подсчитывает общее количество страниц по выгрузке
     *
     * @return float
     */
    private function calcNumberOfPages(){
        return ceil($this->getNumberOfRows()/$this->getRowOnPage());
    }

    public static function getInstance($queryCount = '') {


        if(!self::$_instance) { // If no instance then make one
            self::$_instance = new self($queryCount);
        }

        return self::$_instance;
    }

    // Magic method clone is empty to prevent duplication of connection
    private function __clone() { }

    /**
     * возвращает приставку к запрос
     * для контроля постраничного вывода
     * @return string
     */
    public function getPaginationQueryString(){

        $start = abs($this->getPageFromLimit() * $this->getRowOnPage());

        $query = " LIMIT $start, ".$this->getRowOnPage();

        return $query;
    }

    private function getPageFromLimit(){
        return $this->getPageNumber() - 1;
    }

    /**
     * определяем номер первой страницы в списке ссылок
     *
     * @param $i_centerPageLinkNumber - номер страницы по центру
     * @return float
     * @internal param $maxPaginationLinks
     */
    private function calcPaginationLinksPageStart( $i_centerPageLinkNumber ){
        # получаем максимально разрешённое количество ссылок в блоке  пагинации
        $maxPaginationLinks = $this->getMaxPaginationLinks();

        # определяем максимальное количество ссылок на сбоку от центральной
        $sideLinksNumber    = $this->getSideLinksNumber($maxPaginationLinks);

        # вычитая из номера текущей страницы количество ссылок, которое должно быть сбоку от центара
        # определяем номер первой ссылки в блоке пагинации
        $calcStartPage = $i_centerPageLinkNumber - $sideLinksNumber ;

        # если этот номер больше 0 - то все впорядке, он нам подходит как первый
        if ($calcStartPage > 0){
            # запоминаем номер первой ссылки в ряду пагинации
            $this->setPaginationLinksPageStart($calcStartPage);
            # назначаем центральную ссылку
            $centerPageLinkNumber = $i_centerPageLinkNumber;

        }else{
            # если номер высчитаной первой ссылка меньше или равен нулю (а такой страницы не может быть)
            # присваиваем первой ссылке номер страницы -
            $this->setPaginationLinksPageStart(1);

            # назначаем центральную ссылку
            $centerPageLinkNumber = $sideLinksNumber + 1;
        }

        return $centerPageLinkNumber;
    }

    /**
     * определяем номер последней страницы в списке ссылок
     *
     * @param $centerPageLinkNumber int
     */
    private function calcPaginationLinksPageEnd($centerPageLinkNumber){
        # получаем конесное количество страниц для вывода
        $totalNumberOfPages = $this->getNumberOfPages();

        # получаем максимально разрешённое количество ссылок в блоке  пагинации
        $maxPaginationLinks = $this->getMaxPaginationLinks();

        # определяем максимальное количество ссылок на сбоку от центральной
        $sideLinksNumber    = $this->getSideLinksNumber($maxPaginationLinks);

        # высчитываем номер крайней правой страницы в блоке пагинации
        $calcEndPage        = $centerPageLinkNumber + $sideLinksNumber;

        # если вычисленный номер меньше общего числа страниц
        if ($calcEndPage < $totalNumberOfPages){
            # ставим его
            $this->setPaginationLinksPageEnd( $calcEndPage );
        }else{
            # если больше, то крайнее праве положение будет иметь последняя станица
            $this->setPaginationLinksPageEnd( $totalNumberOfPages );
            # а крайнее левое страница меньше
            $this->setPaginationLinksPageStart( $totalNumberOfPages - $maxPaginationLinks );
        }
    }

    /**
     * определяем максимальное количество ссылок на сбоку от центральной
     *
     * @param $maxPaginationLinks
     * @return int
     */
    private function getSideLinksNumber($maxPaginationLinks){
        return ceil(($maxPaginationLinks - 1)/2);
    }

    /**
     * возвращает html блока пагинации
     *
     * @return string
     */
    public function getPaginationLinks(){
        # получаем параметры для работы
        $pageNumberNow          = $this->getPageNumber();           # номер текущей страницы
        $totalNumberOfPages     = $this->getNumberOfPages();        # всего количество страниц
        $linkFromThisPage       = $this->getLinkFromPage();         # ссылка на текущую страницу
        $maxPaginationLinks     = $this->getMaxPaginationLinks();   # получаем максимально разрешённое число ссылок в блоке пагинаций

        # ссылка с пустым параметром
        $emptyLinkFromOtherPage = $linkFromThisPage.'&'.$this->getPageNumberParameterName();

        # устанавливаем параметры по умолчанию
        $this->setPaginationLinksPageStart(1);                  # № первой страницы в блоке пагинации
        $this->setPaginationLinksPageEnd($totalNumberOfPages);  # № последней страницы в блоке пагинации

        # если количество страниц превышает максимально допустимое количество ссылок на них
        if ($totalNumberOfPages > $maxPaginationLinks){
            # по умолчанию считаем, что в центре у нас указана текущая страница
            $centerPageLinkNumber = $pageNumberNow;
            /**
             * следующие процедуры переопределяют параметры по умаолчанию
             * @paginationLinksPageStart
             * @paginationLinksPageEnd
             */
            # переопределяем центральную ссылку и внутри метода переписываем параметр @paginationLinksPageStart
            $centerPageLinkNumber = $this->calcPaginationLinksPageStart( $centerPageLinkNumber );

            # внутри метода переписываем параметр @paginationLinksPageEnd
            # и в зависимости от ситуации возможно @paginationLinksPageStart
            $this->calcPaginationLinksPageEnd( $centerPageLinkNumber );
        }

        $html = '';

        # условия показа ссылки на предыдущёю страницу
        if( $totalNumberOfPages > $maxPaginationLinks && $pageNumberNow > 1){
            # сборка ссылок
            $html .= $this->getHtmlLink(' <<< ', $emptyLinkFromOtherPage.'=1');
            $html .= $this->getHtmlLink('пред', $emptyLinkFromOtherPage.'='.($pageNumberNow-1));
        }

        # вывод центрального блока ссылок
        for ($pageNumber = $this->getPaginationLinksPageStart(); $pageNumber <= $this->getPaginationLinksPageEnd(); $pageNumber++ ){
            # выделяем текущую страницу
            $classForStyle = ($pageNumber == $pageNumberNow)?'checked':'';

            # собираем сылку на страницу
            $html .= $this->getHtmlLink($pageNumber, $emptyLinkFromOtherPage.'='.$pageNumber, $classForStyle);
        }

        # условие показа ссылки на следующую страницу
        if( $totalNumberOfPages > $maxPaginationLinks && $totalNumberOfPages > $pageNumberNow ){
            # сборка ссылок
            $html .= $this->getHtmlLink('след', $emptyLinkFromOtherPage.'='.($pageNumberNow+1));
            $html .= $this->getHtmlLink(' >>> ', $emptyLinkFromOtherPage.'='.$totalNumberOfPages);
        }
        
        return $this->wrapHtmlContainer( $html );
    }

    /**
     * возвращает HTML ссылки
     *
     * @param string $text
     * @param string $href
     * @param string $class
     * @return string
     */
    private function getHtmlLink( $text = "Page Name", $href="#", $i_class = ""){
        $class = '';
        if ($class != ""){
            $class = ' class="'.$i_class.'"';
        }
        return '<a href="'.$href.'" '.$class.'>'.$text.'</a>';
    }

    /**
     * оборачивает контент в div
     *
     * @param $html
     * @return string
     */
    private function wrapHtmlContainer($html){
        return '<div id="pagination_container">'.$html.'</div>';
    }

    /**
     * возвращает рабочую ссылку на страниц выгрузки
     * @return string
     */
    private function getLinkFromPage(){
        if ($this->getWorkerLink() == ''){
            $newLink = '';
            $i = 0;
            foreach ($_GET as $key => $param){
                if ($key != $this->getPageNumberParameterName()){
                    $newLink .= ($i == 0)?'?':'&';
                    $newLink .= $key.'='.$param;
                }
                $i++;
            }
            $this->setWorkerLink('http://'.$_SERVER['HTTP_HOST'].'/os/'.$newLink);
        }
        return $this->getWorkerLink();

    }

    /**
     * @return string
     */
    public function getWorkerLink()
    {
        return $this->worker_link;
    }

    /**
     * @param string $worker_link
     */
    public function setWorkerLink($worker_link)
    {
        $this->worker_link = $worker_link;
    }

    /**
     * подключение к базе
     *
     * @return mysqli
     */
    private function db(){
        if(!isset($this->mysqli)){
//            include_once $_SERVER['DOCUMENT_ROOT'].'/libs/mysqli.php';

            $db = Database::getInstance();
            $this->mysqli = $db->getConnection();
        }
        return $this->mysqli;
    }



}
