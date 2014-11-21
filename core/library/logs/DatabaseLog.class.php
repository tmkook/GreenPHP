<?php
class DatabaseLog extends Logs
{
    protected $db;
    private $msgs = array();
	
    public function __construct($conf){
		$dsn = $conf['dsn'];
		$this->db = new PDO($dsn,$conf['username'],$conf['password'],
			array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES `{$conf['charset']}`",
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			)
		);
		$this->table = $conf['domain'].'logs';
		$this->db->query("CREATE TABLE  IF NOT EXISTS `{$this->table}`(`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,`msg` TEXT NOT NULL ,`created` INT UNSIGNED NOT NULL ,PRIMARY KEY (`id`)  );");
	}
	
	public function write($msg){
		$created = time();
		$msg = addslashes($msg);
		$sql = "INSERT INTO {$this->table} (`msg`,`created`)VALUES('{$msg}',{$created})";
        return $this->db->query($sql);
	}
    
    public function add($msg){
        $this->msgs[] = $msg;
    }
    
    public function save(){
        foreach($this->msgs as $msg){
            $this->write($msg);
        }
        return true;
    }
    
    public function flush(){
        $sql = "DROP TABLE `{$this->table}`";
        return $this->db->query($sql);
    }

}
