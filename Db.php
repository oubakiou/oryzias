<?php
namespace Oryzias;

use PDO;

abstract class Db
{
    protected static $pdo;
    protected $dbConnectionKey;
    protected $tableName;
    
    public function __construct($dbConnectionKey = null, $dbConfig = null)
    {
        $classNameToken = explode('_', get_class($this));
        $classNameTokenCount = count($classNameToken);
        
        if (!$dbConnectionKey || !$dbConfig) {
            if ($classNameTokenCount == 3) {
                $dbConnectionKey = 'default';
            } elseif ($classNameTokenCount == 4) {
                $dbConnectionKey = $classNameToken[2];
            } else {
                return false;
            }
            $dbConfig = Config::get('db.' . $dbConnectionKey);
        }
        
        if (!isset(self::$pdo[$dbConnectionKey])) {
            $this->connect($dbConnectionKey, $dbConfig);
        }
        
        $this->dbConnectionKey = $dbConnectionKey;
        
        if (!$this->tableName) {
            $this->tableName = array_pop($classNameToken);
        }
    }
    
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 5) == 'getBy') {
            //getByHogeでHogeカラムをキーに1レコード取得
            return $this->getByKey($arguments[0], $this->formatColName(substr($name, 5)));
        } elseif (preg_match('/getAllBy(.*)OrderBy(.*)Desc/', $name, $matches)) {
            //getAllByHogeOrderByFugaDescでHogeカラムをキーにFugaで降順ソートした対象の全レコード取得
            return $this->getAllByKey(
                    $arguments[0],
                    $this->formatColName($matches[1]),
                    $this->formatColName($matches[2]),
                    'DESC'
            );
        } elseif (preg_match('/getAllBy(.*)OrderBy(.*)Asc/', $name, $matches)) {
            //getAllByHogeOrderByFugaDescでHogeカラムをキーにFugaで昇順ソートした対象の全レコード取得
            return $this->getAllByKey(
                    $arguments[0],
                    $this->formatColName($matches[1]),
                    $this->formatColName($matches[2]),
                    'ASC'
            );
        } elseif (substr($name, 0, 8) == 'getAllBy') {
            //getAllByHogeでHogeカラムをキーに対象の全レコード取得
            return $this->getAllByKey($arguments[0], $this->formatColName(substr($name, 8)));
        } elseif (preg_match('/getAll(.*)By(.*)OrderBy(.*)Desc/', $name, $matches)) {
            //getAllHogeByFugaOrderByPiyoDescでFugaカラムをキーPiyoで降順ソートされたHogeカラムの値を取得
            return $this->getAllColByKey(
                    $arguments[0],
                    $this->formatColName($matches[2]),
                    $this->formatColName($matches[1]),
                    $this->formatColName($matches[3]),
                    'DESC'
            );
        } elseif (preg_match('/getAll(.*)By(.*)OrderBy(.*)Asc/', $name, $matches)) {
            //getAllHogeByFugaOrderByPiyoDescでFugaカラムをキーPiyoで昇順ソートされたHogeカラムの値を取得
            return $this->getAllColByKey(
                    $arguments[0],
                    $this->formatColName($matches[2]),
                    $this->formatColName($matches[1]),
                    $this->formatColName($matches[3]),
                    'ASC'
            );
        } elseif (preg_match('/getAll(.*)By(.*)/', $name, $matches)) {
            //getAllHogeByFugaでFugaカラムをキーにHogeカラムの値を取得
            return $this->getAllColByKey(
                    $arguments[0],
                    $this->formatColName($matches[2]),
                    $this->formatColName($matches[1])
            );
        } elseif (preg_match('/get(.*)By(.*)/', $name, $matches)) {
            //getHogeByFugaでFugaカラムをキーにHogeカラムの値を取得
            return $this->getColByKey(
                    $arguments[0],
                    $this->formatColName($matches[2]),
                    $this->formatColName($matches[1])
            );
        } elseif (substr($name, 0, 8) == 'updateBy') {
            //updateByHogeでHogeカラムをキーに1レコード更新
            return $this->updateByKey($arguments[0], $arguments[1], $this->formatColName(substr($name, 8)));
        } elseif (substr($name, 0, 8) == 'deleteBy') {
            //deleteByHogeでHogeカラムをキーに1レコード削除
            return $this->deleteByKey($arguments[0], $this->formatColName(substr($name, 8)));
        } elseif(substr($name, 0, 9) == 'replaceBy') {
            //replaceByHogeでHogeカラムをキーに1レコードリプレース
            return $this->replaceByKey($arguments[0], $arguments[1], $this->formatColName(substr($name, 9)));
        }
        
    }
    
    public function formatColName($colName)
    {
        return lcfirst($colName);
    }
    
    protected function connect($dbConnectionKey, $dbConfig)
    {
        try {
            $dsn = $dbConfig['dsn']['type'] . ':';
            unset($dbConfig['dsn']['type']);
            
            foreach($dbConfig['dsn'] as $k=>$v){
                $tokens[] = $k . '=' . $v;
            }
            $dsn .= implode(';', $tokens);
            
            self::$pdo[$dbConnectionKey] = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
            self::$pdo[$dbConnectionKey]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $log['message'] = $e->getMessage();
            $log['trace'] = $e->getTrace();
            Log::write($log);
        }
    }
    
    //paginator付き
    public function fetchAllWithPaginator($sql, $inputParameters=[], $perPage = 10, $currentPage=1, $pageWidth=3)
    {
        $offset = ($currentPage-1)*$perPage;
        $limit = ' LIMIT ' . intval($offset) . ', ' . intval($perPage) . ' ';//PDO limit bug
        
        if ($data = $this->fetchAll($sql.$limit, $inputParameters)) {
            
            $result['data'] = $data;
            
            $totalHit = $this->fetchOne('SELECT COUNT(*) FROM (' . $sql . ') AS paginator_tmp', $inputParameters);
            $last = ceil($totalHit/$perPage);
            $first = 1;
            
            for ($i=($currentPage-$pageWidth); $i<($currentPage+$pageWidth); $i++) {
                if($i>0 && $i<=$last){
                    $pages[] = $i;
                }
            }
            
            if ($currentPage > $first) {
                $back = $currentPage-1;
            } else {
                $back = false;
            }
            if ($currentPage < $last) {
                $next = $currentPage+1;
            } else {
                $next = false;
            }
            
            if ($currentPage == $first) {
               $paginator['first'] = false;
            } else {
               $paginator['first'] = $first;
            }
            if ($currentPage == $last) {
                $paginator['last'] = false;
            } else {
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
        } else {
            return false;
        }
    }
    
    //プレースホルダーを利用して全件取得
    public function fetchAll($sql, $inputParameters=[])
    {
        if (Config::get('debug')) {
            Log::write(['sql'=>$sql, 'inputParameters'=>$inputParameters]);
        }
        $sth = self::$pdo[$this->dbConnectionKey]->prepare($sql);
        if ($sth->execute($inputParameters)) {
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }
    
    //プレースホルダーを利用して最初の一件取得
    public function fetchRow($sql, $inputParameters=[])
    {
        if ($result = $this->fetchAll($sql, $inputParameters)) {
            return array_shift($result);
        } else {
            return false;
        }
    }
    
    //プレースホルダーを利用して最初の一件の最初のカラム値を取得
    public function fetchOne($sql, $inputParameters=[])
    {
        if ($result = $this->fetchRow($sql, $inputParameters)) {
            return array_shift($result);
        } else {
            return false;
        }
    }
    
    //プレースホルダを利用してSQL直実行
    public function execute($sql, $inputParameters=[])
    {
        if (Config::get('debug')) {
            Log::write(['sql'=>$sql, 'inputParameters'=>$inputParameters]);
        }
        
        $sth = self::$pdo[$this->dbConnectionKey]->prepare($sql);
        return $sth->execute($inputParameters);
    }
    
    public function insert($data)
    {
        if (!isset($data['createdAt'])) {
            $data['createdAt'] = date('Y-m-d H:i:s');
        }
        
        $formatData = self::formatData($data);
        
        $sql =
        'INSERT INTO ' . $this->tableName . ' (' . implode(',', array_keys($data)) . ') ' .
        'VALUES ( ' . implode(',', array_keys($formatData)) . ' )';
        
        if ($this->execute($sql, $formatData)) {
            return self::$pdo[$this->dbConnectionKey]->lastInsertId();
        } else {
            return false;
        }
    }
    
    public function getByKey($keyValue, $keyName='id')
    {
        $sql =
        'SELECT * FROM ' . $this->tableName . ' ' .
        'WHERE ' . $keyName . ' = :' . $keyName;
        
        if ($result = $this->fetchRow($sql, [':'.$keyName=>$keyValue])) {
            return $result;
        } else {
            return false;
        }
    }
    
    public function getAllByKey($keyValue, $keyName='id', $orderCol=null, $orderSeq='DESC')
    {
        if (!$orderCol) {
            $orderCol = $keyName;
        }
        
        $sql =
        'SELECT * FROM ' . $this->tableName . ' ' .
        'WHERE ' . $keyName . ' = :' . $keyName . ' ' .
        'ORDER BY ' . $orderCol . ' ' . $orderSec . ' ';
        
        if ($result = $this->fetchAll($sql, [':'.$keyName=>$keyValue])) {
            return $result;
        } else {
            return false;
        }
    }
    
    public function getColByKey($keyValue, $keyName='id', $selectCol='name')
    {
        if (!$result = $this->getByKey($keyValue, $keyName)) {
            return false;
        }
        
        if (!isset($result[$selectCol])) {
            return false;
        }
        
        return $result[$selectCol];
    }
    
    public function getAllColByKey($keyValue, $keyName='id', $selectCol='name', $orderCol=null, $orderSeq='DESC')
    {
        if (!$temp = $this->getAllByKey($keyValue, $keyName)) {
            return false;
        }
        foreach ($temp as $k=>$v) {
            if (!isset($v[$selectCol])) {
                return false;
            }
            $result[$k] = $v[$selectCol];
        }
        return $result;
    }
    
    public function replaceByKey($data, $keyName = 'id')
    {
        if (isset($data[$keyName]) && $data[$keyName]) {
            //update
            return $this->updateByKey($data[$keyName], $data, $keyName);
        } else {
            //insert
            return $this->insert($data);
        }
    }
    
    public function deleteByKey($keyValue, $keyName='id')
    {
        $sql =
        'DELETE FROM ' . $this->tableName . ' ' .
        'WHERE ' . $keyName . ' = :' . $keyName;
        return $this->execute($sql, [':'.$keyName=>$keyValue]);
    }
    
    public function updateByKey($keyValue, $data, $keyName='id')
    {
        if (!isset($data['updatedAt'])) {
            $data['updatedAt'] = date('Y-m-d H:i:s');
        }
        
        if (isset($data[$keyName])) {
          unset($data[$keyName]);
        }
        
        $formatData = self::formatData($data);
        foreach ($data as $k=>$v) {
            $set[] = $k . '=:' . $k;
        }
        
        $sql =
        'UPDATE ' . $this->tableName . ' ' .
        'SET ' . implode(',', $set) . ' ' .
        'WHERE ' . $keyName . ' = :'.$keyName;
        
        $formatData[':id'] = $keyValue;
        return $this->execute($sql, $formatData);
    }
    
    public static function formatData($data)
    {
        foreach ($data as $k=>$v) {
            $formatData[':' . $k] = $v;
        }
        return $formatData;
    }
    
    public function buildSelect($cond)
    {
        $sql = 'SELECT ';
        if (isset($cond['select'])) {
            $sql .= implode(',', $cond['select']) . " \n";
        } else {
            $sql .= "* \n";
        }
        
        $sql .= 'FROM ';
        if (isset($cond['from'])) {
            $sql .= $cond['from'] . " \n";
        } else {
            $sql .= $this->tableName . " \n";
        }
        
        if (isset($cond['join'])) {
            foreach ($cond['join'] as $join) {
                $sql .= 'INNER JOIN ' . $join[0] . ' ON ' . $join[1] . " \n";
            }
        }
        
        if (isset($cond['leftJoin'])) {
            foreach ($cond['leftJoin'] as $join) {
                $sql .= 'LEFT JOIN ' . $join[0] . ' ON ' . $join[1] . " \n";
            }
        }
        
        if (isset($cond['where'])) {
            $sql .= 'WHERE ' . implode(" \nAND ", $cond['where']) . " \n";
        }
        
        if (isset($cond['groupBy'])) {
            $sql .= 'GROUP BY ' . implode(',', $cond['groupBy']) . " \n";
        }
        if (isset($cond['orderBy'])) {
            $sql .= 'ORDER BY ' . implode(',', $cond['orderBy']) . " \n";
        }
        
        return $sql;
    }
}
