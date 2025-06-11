<?php

namespace App\Action\Todo;

use App\View;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;

/**
 * Action which returns a collection todo entries
 */
class GetAll implements ActionInterface
{
    public function __construct(private View\Todo $view)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        return $this->view->getCollection(
            (int) $request->get('refId'),
            (int) $request->get('startIndex'),
            (int) $request->get('count'),
            $request->get('search'),
        );
    }
}
