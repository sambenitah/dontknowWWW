<?php

namespace DontKnow\Core;

class QueryConstructor{


    const SELECT = 0;
    const UPDATE = 1;
    const INSERT = 2;
    const DELETE = 3;


    public function __construct(SPDO $spdo){
        $this->instance = $spdo->getPDO();
        if(!$this->instance instanceof \PDO)
            throw new \Exception('Aucune connection');
    }


    public function prepare(string $query)
    {
        return $this->instance->prepare($query);
    }

    public function select(array ...$select) //principe fluente  retourne une instance de la class
    {
        $this->requestType = self::SELECT;
        $this->select = $select;
        return $this;
    }

    public function insert(array $insert)
    {
        $this->requestType = self::INSERT;
        $this->insert = $insert;
        return $this;
    }
    public function update(array $update)
    {
        $this->requestType = self::UPDATE;
        $this->update = $update;
        return $this;
    }

    public function delete(string $delete)
    {
        $this->requestType = self::DELETE;
        $this->delete = $delete;
        return $this;
    }

    public function table(string $table)
    {
        $this->table =$table;
        return $this;
    }

    public function from(string $from)
    {
        $this->from = "FROM ".$from;
        return $this;
    }

    public function where(array $where)
    {
        $this->where = $where;
        return $this;
    }

    public function innerJoin(string $innerJoin)
    {
        $this->innerJoin = $innerJoin;
        return $this;
    }

    public function value(string $value)
    {
        $this->value = $value;
        return $this;
    }

    public function count(string $count, string $alias)
    {
        $this->count = $count;
        $this->alias = $alias;
        return $this;
    }

    public function anotheParam(string $param)
    {
        $this->anotherParam = $param;
        return $this;
    }


    public function selectArgs(array $param)
    {

        $arguments = [];


        foreach ($param as $key =>  $value){
            $arguments[] = $key." = :".$key;
        }

        if (isset($arguments)){
            $arguments = "*";
        }else{
            $argumentsString = implode(', ', $arguments);
            $arguments = $argumentsString;
        }
        return $arguments;
    }

    public function updateArgs(array $param){
        $arguments = [];
        foreach ($param as $key => $value) {
            if( $key != "id"){
                $arguments[]=$key."=:".$key;
            }
        }
        $arguments = "SET ".implode(",", $arguments)." WHERE id=:id";
        return $arguments;
    }

    public function insertArgs(array $param){


        $argumentsString = "(".
            implode(", ", array_keys($param) ) ." ) VALUES ( :".
            implode(", :", array_keys($param) ) ." )";



        return $argumentsString;
    }


    public function whereArgs(array $param){

        $arguments = [];

        foreach ($param as $key => $value){
            $arguments[] = $key." = :".$key;
        }

        $arguments = "WHERE  " . implode(" AND ", $arguments) . "";

        return $arguments;
    }


    public function __toString()
    {
        $parts = [];

        if ($this->requestType === QueryConstructor::SELECT){

            $parts[] = "SELECT ";

            if(isset($this->count)){
                $parts[]= "Count(".$this->count.") As ".$this->alias;
            }else{
                $parts[] = $this->selectArgs($this->select);
            }

            $parts[] = $this->from;

            if (isset($this->where))
                $parts[] = $this->whereArgs($this->where);

            $query = implode(' ', $parts);
        }

        if ($this->requestType === QueryConstructor::UPDATE){
            $parts[] = "UPDATE ";
            $parts[] = $this->table;
            $parts[] = $this->updateArgs($this->update);
            if (isset($this->anotheParam))
                $parts[] = $this->anotheParam;
            $query = implode(' ', $parts);
        }

        if ($this->requestType === QueryConstructor::DELETE){
            $parts[] = "DELETE FROM ";
            $parts[] = $this->delete;
            $parts[] = $this->whereArgs($this->where);
            $query = implode(' ', $parts);
        }

        if ($this->requestType === QueryConstructor::INSERT){

            $parts[] = 'INSERT INTO ';
            $parts[] = $this->table;
            $parts[] = $this->insertArgs($this->insert);
            $query = implode(' ', $parts);

        }
        return (string)$query;
    }
}