<?php

namespace Junorz;

use PDO;
use PDOStatement;

class JunDB
{
    public $pdo;

    public function __construct($databaseInfo)
    {
        $DBTYPE = $databaseInfo['type'];
        $SERVER = $databaseInfo['server'];
        $DBNAME = $databaseInfo['dbname'];
        $DBUSER = $databaseInfo['dbuser'];
        $DBPWD = $databaseInfo['pwd'];
        $CHARSET = $databaseInfo['charset'];

        try {
            $conn = "$DBTYPE:host=$SERVER;dbname=$DBNAME;charset=$CHARSET";
            $this->pdo = new PDO($conn, $DBUSER, $DBPWD);
            return $this->pdo;
        } catch (PDOException $e) {
            print 'Error!: ' . $e->getMessage() . '<br/>';
            die();
        }
    }

    /**--------------------------------------------
     * 存储数据
     *---------------------------------------------
     * @param string $table 操作的数据表
     * @param array $storeData 需要存储的数据
     * @return bool 添加成功返回true,反之返回false
     */
    public function add($table, $storeData)
    {
        if (is_array($storeData)) { //传递进来的是数组才会进行处理
            //处理SQL语句
            $query = "INSERT INTO $table(";
            foreach ($storeData as $key => $value) {
                $query = $query . $key . ',';
            }
            $query = substr($query, 0, strlen($query) - 1);
            $query = $query . ') VALUES (';
            for ($i = count($storeData); $i > 0; $i--) {
                $query = $query . '?,';
            }
            $query = substr($query, 0, strlen($query) - 1);
            $query = $query . ')';

            //处理变量绑定
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $prepare = $this->pdo->prepare($query);
            $times = 1;
            foreach ($storeData as $key => $value) {
                ${'value' . $times} = $value;
                $prepare->bindParam($times, ${'value' . $times});
                $times++;
            }
            return $prepare->execute();
        }
    }

    /**--------------------------------------------
     * 根据查找结果返回一个二维数组
     *---------------------------------------------
     * @param string $table 操作的数据表
     * @param array $condition 查找条件
     * @param string $andor 可选择AND/OR
     * @return array 返回一个二维数组
     */
    public function get($table, $condition, $andor = 'and')
    {
        return $this->select($table, $condition, $andor)->fetchAll();
    }

    /**--------------------------------------------
     * 根据查找结果返回第一条数据
     *---------------------------------------------
     * @param string $table 操作的数据表
     * @param array $condition 查找条件
     * @param string $andor 可选择AND/OR
     * @return array 返回第一条数据,是一个一维数组
     */
    public function first($table, $condition, $andor = 'and')
    {
        return $this->select($table, $condition, $andor)->fetch();
    }

    /**--------------------------------------------
     * 为get和first方法提供查找逻辑
     *---------------------------------------------
     * @param string $table 操作的数据表
     * @param array $condition 查找条件
     * @param string $andor 可选择AND/OR
     * @return PDOStatement 返回一个PDOStatement类
     */
    public function select($table, $condition, $andor = 'and')
    {
        //处理SQL语句
        $query = "SELECT * FROM $table WHERE";
        foreach ($condition as $key => $value) {
            if (is_array($value)) {
                $query = $query . " $key IN(";
                foreach ($value as $subvalue) {
                    $query = $query . "?,";
                }
                $query = substr($query, 0, strlen($query) - 1);
                $query = $query . ') ' . $andor;
            } else {
                $query = $query . " $key=? " . $andor;
            }
        }
        if ($andor == 'and') {
            $query = substr($query, 0, strlen($query) - 4);
        } elseif ($andor == 'or') {
            $query = substr($query, 0, strlen($query) - 3);
        }

        //处理变量绑定
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $prepare = $this->pdo->prepare($query);
        $times = 1;
        foreach ($condition as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subvalue) {
                    ${'value' . $times} = $subvalue;
                    $prepare->bindParam($times, ${'value' . $times});
                    $times++;
                }
            } else {
                ${'value' . $times} = $value;
                $prepare->bindParam($times, ${'value' . $times});
                $times++;
            }
        }
        $prepare->execute();
        return $prepare;
    }

    /**--------------------------------------------
     * 更新数据
     *---------------------------------------------
     * @param string $table 操作的数据表
     * @param array $setData 需要更新的数据
     * @param array $selectData 查找条件
     * @return bool 操作成功返回true,反之返回false
     */
    public function update($table, $setData, $selectData)
    {
        if (is_array($setData) && is_array($selectData)) {
            $query = "UPDATE $table SET";
            foreach ($setData as $setKey => $setValue) {
                $query = $query . " $setKey=:$setKey,";
            }
            $query = substr($query, 0, strlen($query) - 1);
            $query = $query . ' WHERE';
            foreach ($selectData as $selectKey => $selectValue) {
                $query = $query . " $selectKey=:$selectKey";
            }

            //处理变量绑定
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $prepare = $this->pdo->prepare($query);
            foreach ($setData as $setKey => $setValue) {
                ${$setKey} = $setValue;
                $prepare->bindParam(":$setKey", ${$setKey});
            }
            foreach ($selectData as $selectKey => $selectValue) {
                ${$selectKey} = $selectValue;
                $prepare->bindParam(":$selectKey", ${$selectKey});
            }
            return $prepare->execute();
        }

    }

    /**--------------------------------------------
     * 删除数据
     *---------------------------------------------
     * @param string $table 操作的数据表
     * @param array $delData 查找条件
     * @param string $andor 可选择AND/OR
     * @return bool 操作成功返回true,反之返回false
     */
    public function del($table, $delData, $andor = 'and')
    {
        //处理SQL语句
        $query = "DELETE FROM $table WHERE";
        foreach ($delData as $key => $value) {
            if (is_array($value)) {
                $query = $query . " $key IN(";
                for ($i = count($value); $i > 0; $i--) {
                    $query = $query . '?,';
                }
                $query = substr($query, 0, strlen($query) - 1);
                $query = $query . ') ' . $andor;
            } else {
                $query = $query . " $key=? " . $andor;
            }

        }
        if ($andor == 'and') {
            $query = substr($query, 0, strlen($query) - 4);
        } elseif ($andor == 'or') {
            $query = substr($query, 0, strlen($query) - 3);
        }

        //处理变量绑定
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $prepare = $this->pdo->prepare($query);
        $times = 1;
        foreach ($delData as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subvalue) {
                    ${'value' . $times} = $subvalue;
                    $prepare->bindParam($times,${'value' . $times});
                    $times++;
                }
            } else {
                ${'value' . $times} = $value;
                $prepare->bindParam($times,${'value' . $times});
                $times++;
            }
        }
        return $prepare->execute();
    }

    /**--------------------------------------------
     * 安全地执行一条SQL语句
     *---------------------------------------------
     * @param string $query SQL语句
     * @param array $bindData 要绑定的数据
     * @return PDOStatement 返回一个PDOStatement类
     */
    public function safe($query, $bindData)
    {
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $prepare = $this->pdo->prepare($query);
        if (is_array($bindData)) {
            foreach ($bindData as $key => $value) {
                ${$key} = $value;
                $prepare->bindParam(":$key", ${$key});
            }
            $prepare->execute();
            return $prepare;
        }
    }


}