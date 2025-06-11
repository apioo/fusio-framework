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
    $operation->setDescription('Updates a single todo');
    $operation->setHttpMethod(HttpMethod::PUT);
    $operation->setHttpPath('/todo/:id');
    $operation->setHttpCode(200);
    $operation->setIncoming(Model\Todo::class);
    $operation->setOutgoing(Model\Message::class);
    $operation->addThrow(500, Model\Message::class);
    $operation->setAction(Action\Todo\Update::class);
};
