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
    $operation->setDescription('Returns all available todos');
    $operation->setHttpMethod(HttpMethod::GET);
    $operation->setHttpPath('/todo');
    $operation->setHttpCode(200);
    $operation->addParameter('startIndex', PropertyTypeFactory::getInteger());
    $operation->addParameter('count', PropertyTypeFactory::getInteger());
    $operation->addParameter('search', PropertyTypeFactory::getString());
    $operation->setOutgoing(Model\TodoCollection::class);
    $operation->addThrow(500, Model\TodoCollection::class);
    $operation->setAction(Action\Todo\GetAll::class);
};
