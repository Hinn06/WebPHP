<?php
namespace App\Repository;
class FacultyRepository {public function __construct(private \PDO $pdo){} public function findAll():array{return $this->pdo->query('SELECT * FROM faculties ORDER BY name')->fetchAll();} public function findById(int $id):?array{$s=$this->pdo->prepare('SELECT * FROM faculties WHERE id=:id');$s->execute(['id'=>$id]);return$s->fetch()?:null;} public function create(string $code,string $name):int{$s=$this->pdo->prepare('INSERT INTO faculties(code,name) VALUES(:c,:n)');$s->execute(['c'=>$code,'n'=>$name]);return(int)$this->pdo->lastInsertId();}}
