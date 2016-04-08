<?php
namespace atk4\dsql\tests;
use atk4\dsql\Query;
use atk4\dsql\Expression;
use atk4\dsql\Migration;



class MigrationTest extends \PHPUnit_Extensions_Database_TestCase
{
    protected $pdo;
    function __construct()
    {
        $this->pdo = new \PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
        $this->pdo->query('create temporary table employee (id int, name text, surname text, retired bool)');
    }
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->pdo, $GLOBALS['DB_DBNAME']);
    }
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__).'/SelectTest.xml');
    }
    private function m(){
        return new Migration(['connection'=>$this->pdo]);
    }
    private function q(){
        return new Query(['connection'=>$this->pdo]);
    }
    private function e($template=null,$args=null){
        return new Expression(['connection'=>$this->pdo]);
    }
    public function testBasicMigrations()
    {
        return;
        // drop old table first
        $this->m()->table('user')
            ->drop('if exists');

        $this->m()->table('user')
            ->addColumn('name',['size'=>50])
            ->addColumn('email,password')
            ->addColumn('string','string')
            ->addColumn('text','text') // string-based
            ->addColumn('integer','integer')
            ->addColumn('float','float')
            ->addColumn('money','money') // float-based
            ->addColumn('boolean','boolean')
            ->addColumn('date','date')
            ->addColumn('time','time')
            ->addColumn('datetime','datetime')
            ->addColumn('timestamp','timestamp')

            ->create();

        $this->m()->table('user')
            ->changeColumn('name')
            ->addColumn('surname')
            ->addColumn('surname')
            ->execute();

        $this->m()->table('user')
            ->dropColumn('string,text,integer,float,money,boolean,date,time,datetime,timestamp')
            ->execute();

        $this->q()->table('user')
            ->set('name','Darcy')
            ->set('surname','Wild')
            ->insert();

        $this->q()->table('user')
            ->set(['name'=>'Brett','surname'=>'Bird'])
            ->insert();

        $this->q()->table('user')
            ->insert([
                ['name'=>'Jason','surname'=>'Wild'],
                ['name'=>'Juliet','surname'=>'Wild'],
                ['name'=>'James','surname'=>'Knight'],
            ])
            ->insert();
    }
}

