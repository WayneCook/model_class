<?php

/**
 * User: Wayne
 * Date: 10/22/2018
 * Time: 22:03
 */

abstract class Model
{
  private $connection;
  private $query;
  private $error;
  private $results;
  private $count;

  //Set PDO connection instance
  public function __construct(Connection $connection)
  {
    $this->connection = $connection->connect();
  }

  //Used to perform sql query
  public function query(string $sql, array $params = array())
  {
    $this->error = false;
    if($this->query = $this->connection->prepare($sql)){
      $x = 1;
      if(count($params)){
        foreach ($params as $param) {
          $this->query->bindValue($x, $param);
          $x++;
        }
      }
      if($this->query->execute()){
        $this->results = $this->query->fetchAll(\PDO::FETCH_OBJ);
        $this->count = $this->query->rowCount();
      } else {
        $this->error = true;
      }
    }
    return $this;
  }

  //Query action
  public function action(string $action, string $table, array $where = array())
  {
    if (count($where) === 3) {

      $operators = array('=', '>', '<', '>=', '<=');

      $field    = $where[0];
      $operator = $where[1];
      $value    = $where[2];

      if (in_array($operator, $operators)) {

        $sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";

        if (!$this->query($sql, array($value))->error()) {
          return $this;
        }
      }
      return false;
    }
  }

  public function get($input)
  {
    if (is_integer($input)) {
      return $this->action('SELECT * ', $this->table, array('id', '=', $input))->results();

    }
    return $this->action('SELECT * ', $this->table, $where)->results();
  }

  public function results()
  {
    return $this->results;
  }

  public function error(): bool
  {
    return $this->error;
  }

  public function first()
  {
    return $this->results[0];
  }

  public function all()
  {
    return $this->query('SELECT * FROM ' . $this->table)->results();
  }

}
