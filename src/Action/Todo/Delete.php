<?php

namespace App\Action\Todo;

use App\Model\Message;
use App\Service;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;

/**
 * Action to delete a todo entry
 */
readonly class Delete implements ActionInterface
{
    public function __construct(private Service\Todo $service)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): Message
    {
        $id = $this->service->delete(
            (int) $request->get('id')
        );

        $message = new Message();
        $message->setSuccess(true);
        $message->setMessage('Todo successfully deleted');
        $message->setId($id);

        return $message;
    }
}
