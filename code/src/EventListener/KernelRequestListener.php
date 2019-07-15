<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class KernelRequestListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }
        $event->getRequest()->setRequestFormat('json');
    }
}
