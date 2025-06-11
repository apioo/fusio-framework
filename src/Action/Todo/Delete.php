<?php

namespace App\Action\Todo;

use App\Model\Message;
use App\Service;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Response\FactoryInterface;
use PSX\Http\Exception\InternalServerErrorException;
use PSX\Http\Exception\StatusCodeException;

/**
 * Action to delete a todo entry
 */
readonly class Delete implements ActionInterface
{
    public function __construct(private Service\Todo $service, private FactoryInterface $response)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        try {
            $id = $this->service->delete(
                (int) $request->get('id')
            );

            $message = new Message();
            $message->setSuccess(true);
            $message->setMessage('Todo successful deleted');
            $message->setId($id);
        } catch (StatusCodeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new InternalServerErrorException($e->getMessage());
        }

        return $this->response->build(200, [], $message);
    }
}
