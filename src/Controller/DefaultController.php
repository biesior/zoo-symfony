<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(): Response
    {

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/config", name="config")
     */
    public function config(): Response
    {
        var_dump($this->getParameter('chat_api'));

//        var_dump($cfg);
        return new Response('conf');
    }

    /**
     * @Route("/say-hello", name="say_hello")
     */
    public function sayHello(): Response
    {
        return new Response('hellou\' world!');
    }

    /**
     * @Route("/time", name="default_time")
     */
    public function time()
    {
        $time=new \DateTime();
       return $this->json(['time'=> $time->format('d.m.Y H:i:s')]);
    }
}
