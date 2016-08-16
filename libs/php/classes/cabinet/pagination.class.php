<?php

/**
 * Class Pagination
 *
 * User: Alexey Kapitonov
 * Date: 15.08.16
 * Time: 11:30
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
     * @param int $numberOfRows
     */
    public function setNumberOfRows($queryForCount)
    {
        if ($queryForCount == ''){
//           exit('для модуля пагинации требуется запрос для определения общего количество строк');

        }else{
            $this->q = $queryForCount;
        }

        $numberOfRows = 0;

        $this->db();
        $result = $this->mysqli->query($this->q) or die($this->mysqli->error);

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
     * @param int $pageNumber
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
        if(isset($_GET['page_number'])){
            $this->setPageNumber((int)$_GET['page_number']);
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


    public function getPaginationHtml() {
        // $this->db();
    }
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


    private function calcPaginationLinksPageStart($calcPageNumberNow, $maxPaginationLinks ){

        # определяем номер первой страницы в списке ссылко
        $calcStartPage = ($calcPageNumberNow - (($maxPaginationLinks - 1)/2)) ;
        if ($calcStartPage > 0){
            $this->paginationLinksPageStart = $calcStartPage;
        }else{
            $this->paginationLinksPageStart = 1;
            $calcPageNumberNow = (($maxPaginationLinks - 1)/2)+1;
        }

        return $calcPageNumberNow;
    }
    private function calcPaginationLinksPageEnd($calcPageNumberNow,$maxPaginationLinks,$totalNumberOfPages){
        $calcEndPage = ($calcPageNumberNow + (($maxPaginationLinks - 1)/2)) ;

        if ($calcEndPage < $totalNumberOfPages){
            $this->paginationLinksPageEnd = $calcEndPage;

        }else{
            $this->paginationLinksPageEnd = $totalNumberOfPages;
            $this->paginationLinksPageStart = $totalNumberOfPages - $maxPaginationLinks;
        }
    }

    /**
     * возвращает html блока пагинации
     *
     * @return string
     */
    public function getPaginationLinks(){
        $pageNumberNow      = $this->getPageNumber();
        $totalNumberOfPages = $this->getNumberOfPages();
        $linkFromPage       = $this->getLinkFromPage();
        $maxPaginationLinks = $this->getMaxPaginationLinks();

        $html = '<div id="pagimation_conteiner">';

        # условия показа ссылки на предыдущёю страницу
        if( $totalNumberOfPages > $maxPaginationLinks && $pageNumberNow > 1){
            $html .= '<a style="padding:5px;" href="http://'.$linkFromPage.'&page_number=1"> <<< </a>';
            $html .= '<a style="padding:5px;" href="http://'.$linkFromPage.'&page_number='.($pageNumberNow-1).'"> пред. </a>';
        }

        $this->paginationLinksPageStart = 1;
        $this->paginationLinksPageEnd = $totalNumberOfPages;

        # если количество ссылок показываемых клиенту необходимо сократить
        if ($totalNumberOfPages > $maxPaginationLinks){
            # определяем первую ссылку в списке
            $calcPageNumberNow = $this->calcPaginationLinksPageStart($pageNumberNow, $maxPaginationLinks );

            # определяем последнюю ссылку в списке
            $this->calcPaginationLinksPageEnd($calcPageNumberNow,$maxPaginationLinks,$totalNumberOfPages);
        }


        for ($pageNumber = $this->paginationLinksPageStart; $pageNumber <= $this->paginationLinksPageEnd; $pageNumber++ ){
            $class = ($pageNumber == $pageNumberNow)?' class="checked"':'';
            $html .= '<a style="padding:5px;" href="http://'.$linkFromPage.'&page_number='.$pageNumber.'" '.$class.'>'.$pageNumber.'</a>';
        }



        # условие показа ссылки на следующую страницу
        if( $totalNumberOfPages > $maxPaginationLinks && $totalNumberOfPages > $pageNumberNow ){
            $html .= '<a style="padding:5px;" href="http://'.$linkFromPage.'&page_number='.($pageNumberNow+1).'"> след. </a>';
            $html .= '<a style="padding:5px;" href="http://'.$linkFromPage.'&page_number='.$totalNumberOfPages.'"> >>> </a>';
        }

        $html .= '</div>';
        
        return $html;
    }

    /**
     * возхвращает рабочую ссылку на страниц выгрузки
     * @return string
     */
    private function getLinkFromPage(){
        if ($this->getWorkerLink() == ''){
            $newLink = '';
            $i = 0;
            foreach ($_GET as $key => $param){
                if ($key != 'page_number'){
                    $newLink .= ($i == 0)?'?':'&';
                    $newLink .= $key.'='.$param;
                }
                $i++;
            }
            $this->setWorkerLink($_SERVER['HTTP_HOST'].'/os/'.$newLink);
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
            $db = Database::getInstance();
            $this->mysqli = $db->getConnection();
        }
        return $this->mysqli;
    }



}
