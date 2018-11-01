<?php

/**
 * User: Wayne Cook
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

        if (strpos($sql, 'SELECT') !== false) {

          $this->results = $this->query->fetchAll(\PDO::FETCH_OBJ);
          $this->count = $this->query->rowCount();
        }
      } else {
        $this->error = true;
      }
    }
    return $this;
  }

  //query builder
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

  //Used to insert new record.
  //Usage ex: $user->insert(array('field' => 'value'))
  public function insert(array $values): bool
  {
    if (count($values)) {

      $fields = array_keys($values);
      $values = array_values($values);
      $placeHolders = implode(', ', array_fill(0, sizeof($values), '?'));

      $sql = "INSERT INTO " . $this->table . " (`" . implode('`, `', $fields) . "`) VALUES ($placeHolders)";

      if (!$this->query($sql, $values)->error()) {
        return true;
      }
      return false;
    }
  }

  //Used to update records
  //Usage ex: $user->update(1,array('field' => 'value'))
  public function update(int $id, array $values): bool
  {

    if (is_integer($id) && !empty($values)) {

      $fields = array_keys($values);
      $values = array_values($values);
      $placeHolders = implode(', ', array_fill(0, sizeof($values), '?'));

      $sql = "UPDATE {$this->table} SET `" . implode('`= ?, `', $fields) ."`= ? WHERE id = {$id}";

      if (!$this->query($sql, $values)->error()) {

        return true;
      }
    }

    return false;
  }

  //Used to fetch by id or by field.
  //Usage ex: $user->get(array('field', '=', 'value'))
  //Usage ex: $user->get(1);
  public function get($input)
  {
    if (is_integer($input)) {
      return $this->action('SELECT * ', $this->table, array('id', '=', $input))->results();

    }
    return $this->action('SELECT * ', $this->table, $input)->results();
  }

  //Used to delete records by id or by field
  //Usage ex: $user->delete(array('field', '=', 'value'))
  //Usage ex: $user->delete(1);
  public function delete($input)
  {
    if (is_integer($input)) {

      return $this->action('DELETE ', $this->table, array('id', '=', $input))->error();

    }
    return $this->action('DELETE ', $this->table, $input)->error();
  }

  //get all records
  public function all()
  {
    return $this->query('SELECT * FROM ' . $this->table)->results();
  }

  //get results from select query
  public function results()
  {
    return $this->results;
  }

  //check for query errors
  public function error(): bool
  {
    return $this->error;
  }

  //return only first record
  public function first()
  {
    return $this->results[0];
  }

}
