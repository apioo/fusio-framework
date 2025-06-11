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
    $operation->setPublic(false);
    $operation->setDescription('Creates a new todo');
    $operation->setHttpMethod(HttpMethod::POST);
    $operation->setHttpPath('/todo');
    $operation->setHttpCode(201);
    $operation->setIncoming(Model\Todo::class);
    $operation->setOutgoing(Model\TodoCollection::class);
    $operation->addThrow(500, Model\TodoCollection::class);
    $operation->setAction(Action\Todo\Create::class);
};
