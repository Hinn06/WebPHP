<?php
namespace App\Core;
abstract class Controller {
    protected function view(string $name, array $data=[]): void { extract($data); require dirname(__DIR__) . '/View/' . $name . '.php'; }
}
