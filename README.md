# JunDB
A PHP class for CRUD database easily.   
一个可以让你简单对数据库进行CRUD操作的PHP库。

## Installation / 安装
Download the JunDB.php to your project and include it.  
use 'Junorz\JunDB' for namespace issue.  
下载JunDB.php到你的工程里，并使用use关键字引用它来解决命名空间的一些问题。  
```
include_once 'JunDB.php';
use Junorz\JunDB;
```

##Usage / 使用方法
####Initializer / 初始化
```
$mydb = new JunDB([
    'type' => 'mysql',
    'server' => 'localhost',
    'dbname' => 'database',
    'dbuser' => 'root',
    'pwd' => 'secret',
    'charset' => 'utf8'
]);
```
####Select / 查询
1.Get the first record.This will return a one dimensional array.  
The third parameter `or` can be omitted which defaults to `and`.  
取得第一条记录。返回的是一个一维的数组。
第三个参数`or`可以被省略。它的默认值为`and`。
```
$data = $mydb->first('usertable',[
    'name' => 'Jun',
    'id' => '10'
],'or');
```
This equals `SELECT * FROM usertable WHERE name='Jun' or id=10`.  

2.Get all records.This will return a two-dimensional array.
取得所有记录。返回的是一个二维数组。  
```
$data = $mydb->get('usertable',[
    'name' => 'Jun',
    'id' => '10'
]);
```
This equals `SELECT * FROM usertable WHERE name='Jun' and id=10`.  

####Insert / 添加数据
```
$data = $mydb->add('usertable',[
    'name' => 'New',
    'email' => 'new@domain.com'
]);
```
This equals `INSERT INTO usertable (name, email) VALUES ('New','new@domain.com')`

####Update / 更新数据
```
$data = $mydb->update('usertable',[
    'name' => 'newupdate',
    'email' => 'newupdate@domain.com'
],[
    'id' => 50
]);
```
This equals `UPDATE usertable SET name='newupdate',email='newupdate@domain.com' WHERE id=50`
####Delete / 删除
```
$data = $mydb->del('usertable', [
    'name' => '',
    'id' => [50, 51],
    'email' => 'new@domain.com'
], 'or');
```
This equals `DELETE FROM usertable WHERE name='' or id IN(50,51) or email='new@domain.com'`   
If you omit the third parameter,it will equal to`DELETE FROM usertable WHERE name='' and id IN(50,51) and email='new@domain.com'`

####All / 返回所有记录
Use `all` function to get all records.
使用`all`方法来返回数据表的所有记录。
```
$data = $mydb->all('usertable');
```

####execute SQL safely / 安全地执行一条SQL语句
Use an array to bine data appear in SQL syntax.  
The `safe` function returns a PDOStatement class,so you can do `fetch()` to get first record,or you can do `fetchAll()` to get all records.   
使用了一个数组来对SQL语句里的数据进行了绑定。   
`safe`方法返回的是一个PDOStatement类，你可以使用`fetch()`来取得第一条记录，或使用`fetchAll()`来取得所有记录。
```
$sql = "SELECT id,name FROM users WHERE email=:email or id IN (:id1,:id2,:id3)";
$data = $mydb->safe($sql,[
    'email' => 'mail@domain.com',
    'id1' => '5',
    'id2' => '9',
    'id3' => '20'
])->fetchAll();
```

##LICENSE / 开源协议
The JunDB is open-sourced software licensed under the MIT license.
