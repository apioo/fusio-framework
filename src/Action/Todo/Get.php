<?php

namespace App\Action\Todo;

use App\View;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;

/**
 * Action which returns a specific todo entry
 */
class Get implements ActionInterface
{
    public function __construct(private View\Todo $view)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        return $this->view->getEntity(
            (int) $request->get('id')
        );
    }
}
