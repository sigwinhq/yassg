<?php

namespace Sigwin\YASSG\Bridge\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    public function __invoke(RequestStack $requestStack)
    {
        $request = $requestStack->getMainRequest();
        
        $route = $request->attributes->get('_route');
        
        return $this->render(sprintf('pages/%1$s.html.twig', $route), $request->attributes->all());
    }
}
