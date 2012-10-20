<?php
namespace Oryzias;
use PDO;
abstract class Db{

    protected static $pdo;
    protected $dbConnectionKey;
    protected $tableName;

    public function __construct($dbConnectionKey, $dbConfig){

        if(!isset(self::$pdo[$dbConnectionKey])){
            $this->connect($dbConnectionKey, $dbConfig);
        }

        $this->tableName = array_pop(explode('_', get_class($this)));

    }

    public function __call($name, $arguments){
        //getByHogeでHogeカラムをキーに1レコード取得
        if(substr($name, 0, 5) == 'getBy'){
            return $this->getByKey($arguments[0], lcfirst(substr($name, 5)));
        }
        //getAllByHogeでHogeカラムをキーに対象の全レコード取得
        if(substr($name, 0, 8) == 'getAllBy'){
            return $this->getAllByKey($arguments[0], lcfirst(substr($name, 8)));
        }
        //getHogeByFugaでFugaカラムをキーにHogeカラムの値を取得
        elseif(preg_match('/get(.*)By(.*)/', $name, $matches)){
            return $this->getColByKey($arguments[0], lcfirst($matches[2]), lcfirst($matches[1]));
        }
        //updateByHogeでHogeカラムをキーに1レコード更新
        elseif(substr($name, 0, 8) == 'updateBy'){
            return $this->updateByKey($arguments[0], $arguments[1], lcfirst(substr($name, 8)));
        }
        //deleteByHogeでHogeカラムをキーに1レコード削除
        elseif(substr($name, 0, 8) == 'deleteBy'){
            return $this->deleteByKey($arguments[0], lcfirst(substr($name, 8)));
        }
        //replaceByHogeでHogeカラムをキーに1レコードリプレース
        elseif(substr($name, 0, 9) == 'replaceBy'){
            return $this->replaceByKey($arguments[0], $arguments[1], lcfirst(substr($name, 9)));
        }

    }

    protected function connect($dbConnectionKey, $dbConfig){
        try {
            $dsn = $dbConfig['type'] . ':dbname=' . $dbConfig['name'] . ';host=' . $dbConfig['serverName'];
            self::$pdo[$dbConnectionKey] = new PDO($dsn, $dbConfig['userName'], $dbConfig['userPassword']);
            self::$pdo[$dbConnectionKey]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbConnectionKey = $dbConnectionKey;
        }
        catch (PDOException $e) {
        	throw $e;
        }
    }

    //paginator付き
    public function fetchAllWithPaginator($sql, $inputParameters=array(), $perPage = 10, $currentPage=1, $pageWidth=3){

        $offset = ($currentPage-1)*$perPage;
        $limit = ' LIMIT ' . intval($offset) . ', ' . intval($perPage) . ' ';//PDO limit bug

        if($data = $this->fetchAll($sql.$limit, $inputParameters)){

            $result['data'] = $data;

            $totalHit = $this->fetchOne('SELECT COUNT(*) FROM (' . $sql . ') AS paginator_tmp', $inputParameters);
            $last = ceil($totalHit/$perPage);
            $first = 1;

            for($i=($currentPage-$pageWidth); $i<($currentPage+$pageWidth); $i++){
                if($i>0 && $i<=$last){
                    $pages[] = $i;
                }
            }

            if($currentPage > $first){
                $back = $currentPage-1;
            }else{
                $back = false;
            }
            if($currentPage < $last){
                $next = $currentPage+1;
            }else{
                $next = false;
            }

            if($currentPage == $first){
               $paginator['first'] = false;
            }else{
               $paginator['first'] = $first;
            }
            if($currentPage == $last){
                $paginator['last'] = false;
            }else{
                $paginator['last'] = $last;
            }

            $paginator['next'] = $next;
            $paginator['back'] = $back;
            $paginator['totalHit'] = $totalHit;
            $paginator['offset'] =$offset;
            $paginator['pages'] = $pages;
            $paginator['currentPage'] = $currentPage;

            $result['paginator'] = $paginator;

            return $result;
        }else{
            return false;
        }
    }

    //プレースホルダーを利用して全件取得
    public function fetchAll($sql, $inputParameters=array()){
        $sth = self::$pdo[$this->dbConnectionKey]->prepare($sql);
        if($sth->execute($inputParameters)){
            return $sth->fetchAll();
        }else{
            return false;
        }
    }

    //プレースホルダーを利用して最初の一件取得
    public function fetchRow($sql, $inputParameters=array()){
        if($result = $this->fetchAll($sql, $inputParameters)){
            return $result[0];
        }else{
            return false;
        }
    }

    //プレースホルダーを利用して最初の一件の最初のカラム値を取得
    public function fetchOne($sql, $inputParameters=array()){
        if($result = $this->fetchRow($sql, $inputParameters)){
            return $result[0];
        }else{
            return false;
        }
    }

    //プレースホルダを利用してSQL直実行
    public function execute($sql, $inputParameters=array()){
        $sth = self::$pdo[$this->dbConnectionKey]->prepare($sql);
        return $sth->execute($inputParameters);
    }

    public function insert($data){

        if(!isset($data['createdAt'])){
            $data['createdAt'] = date('Y-m-d H:i:s');
        }

        $formatData = self::formatData($data);

        $sql =
        'INSERT INTO ' . $this->tableName . ' (' . implode(',', array_keys($data)) . ') ' .
        'VALUES ( ' . implode(',', array_keys($formatData)) . ' )';

        if($this->execute($sql, $formatData)){
            return self::$pdo[$this->dbConnectionKey]->lastInsertId();
        }else{
            return false;
        }
    }

    public function getByKey($keyValue, $keyName='id'){
        $sql =
        'SELECT * FROM ' . $this->tableName . ' ' .
        'WHERE ' . $keyName . ' = :' . $keyName;

        if($result = $this->fetchRow($sql, array(':'.$keyName=>$keyValue))){
            return $result;
        }else{
            return false;
        }
    }

    public function getAllByKey($keyValue, $keyName='id'){
        $sql =
        'SELECT * FROM ' . $this->tableName . ' ' .
        'WHERE ' . $keyName . ' = :' . $keyName;
    
        if($result = $this->fetchAll($sql, array(':'.$keyName=>$keyValue))){
            return $result;
        }else{
            return false;
        }
    }
    
    public function getColByKey($keyValue, $keyName='id', $colName='name'){

        if(!$result = $this->getByKey($keyValue, $keyName)){
            return false;
        }

        if(!isset($result[$colName])){
            return false;
        }

        return $result[$colName];
    }

    public function replaceByKey($data, $keyName = 'id'){
        //update
        if(isset($data[$keyName]) && $data[$keyName]){
          return $this->updateByKey($data[$keyName], $data, $keyName);
        }
        //insert
        else{
          return $this->insert($data);
        }
    }

    public function deleteByKey($keyValue, $keyName='id'){
        $sql =
        'DELETE FROM ' . $this->tableName . ' ' .
        'WHERE ' . $keyName . ' = :' . $keyName;
        return $this->execute($sql, array(':'.$keyName=>$keyValue));
    }

    public function updateByKey($keyValue, $data, $keyName='id'){

        if(!isset($data['updatedAt'])){
            $data['updatedAt'] = date('Y-m-d H:i:s');
        }

        if(isset($data[$keyName])){
          unset($data[$keyName]);
        }

        $formatData = self::formatData($data);
        foreach($data as $k=>$v){
            $set[] = $k . '=:' . $k;
        }

        $sql =
        'UPDATE ' . $this->tableName . ' ' .
        'SET ' . implode(',', $set) . ' ' .
        'WHERE ' . $keyName . ' = :'.$keyName;

        $formatData[':id'] = $keyValue;
        return $this->execute($sql, $formatData);
    }

    static public function formatData($data){
        foreach($data as $k=>$v){
            $formatData[':' . $k] = $v;
        }
        return $formatData;
    }

}