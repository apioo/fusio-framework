<?php

namespace App\Action\Todo;

use App\Model\Message;
use App\Service;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;

/**
 * Action which updates a todo entry
 */
readonly class Reminder implements ActionInterface
{
    public function __construct(private Service\Todo $service)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): Message
    {
        $this->service->reminder();

        $message = new Message();
        $message->setSuccess(true);
        $message->setMessage('Todo reminder successfully executed');

        return $message;
    }
}
