<?php

namespace App\Action\Todo;

use App\Model\Message;
use App\Service;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Response\FactoryInterface;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * Action to create a todo entry
 */
readonly class Create implements ActionInterface
{
    public function __construct(private Service\Todo $service, private FactoryInterface $response)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
    {
        $id = $this->service->create(
            $request->getPayload(),
            $context
        );

        $message = new Message();
        $message->setSuccess(true);
        $message->setMessage('Todo successfully created');
        $message->setId($id);

        return $this->response->build(201, [], $message);
    }
}
