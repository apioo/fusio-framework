<?php

use App\Action;
use App\Model;
use Fusio\Cli\Builder\Operation;
use Fusio\Cli\Builder\Operation\HttpMethod;
use Fusio\Cli\Builder\Operation\Stability;
use PSX\Schema\Type\Factory\PropertyTypeFactory;

return function (Operation $operation) {
    $operation->setScopes(["todo"]);
    $operation->setStability(Stability::EXPERIMENTAL);
    $operation->setPublic(true);
    $operation->setDescription('Returns a single todo');
    $operation->setHttpMethod(HttpMethod::GET);
    $operation->setHttpPath('/todo/:id');
    $operation->setHttpCode(200);
    $operation->setOutgoing(Model\Todo::class);
    $operation->addThrow(500, Model\Todo::class);
    $operation->setAction(Action\Todo\Get::class);
};
