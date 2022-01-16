<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Bbdd extends \Codeception\Module
{
  public function selectAll(string $table, array $criteria = [], string $column = '*')
  {
    // $driver = $this->getModule('Db')->driver;
    $driver = $this->getModule('Db')->_getDriver(); // Error de Intelephense - No es error real
    $query = $driver->select($column, $table, $criteria);
    $parameters = array_values($criteria);
    $this->debugSection('Query', $query);
    if (!empty($parameters)) {
        $this->debugSection('Parameters', $parameters);
    }
    $sth = $driver->executeQuery($query, $parameters);
    return $sth->fetchAll(\PDO::FETCH_ASSOC);
    // return $sth->fetchAll(\PDO::FETCH_COLUMN, 0);
  }
}
