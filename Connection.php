<?php

final class Connection
{

  protected $connection = null;
  protected $host       = 'localhost';
  protected $username   = 'root';
  protected $password   = '';
  protected $db         = 'user_login';

  public function connect()
  {
    if (is_null($this->connection))
    {
      try
      {
        $this->connection = new \PDO('mysql:host=' . $this->host . ';dbname=' . $this->db, $this->username, $this->password);
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      } catch(\PDOException $e)
        {
          die('Connection failed: ' . $e->getMessage());
        }
    }
    return $this->connection;
  }
}
