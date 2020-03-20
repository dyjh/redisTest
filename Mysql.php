<?php


class Mysql
{
    /**
     * @var PDO
     */
    private $conn;

    public function __construct()
    {
        $host = '127.0.0.1';
        $database = 'redisTest';
        $username = 'root';
        $password = 'dyjh123456';
        $this->conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $this->conn->exec("set names 'utf8'");
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function run_select($sql)
    {
        $stmt = $this->conn->prepare($sql);
        $rs = $stmt->execute();
        //print_r($rs);
        if ($rs) {
            // PDO::FETCH_ASSOC 关联数组形式
            // PDO::FETCH_NUM 数字索引数组形式
//            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                //print_r($row);die;
//            }
            //print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public function run_edit($sql)
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }
}