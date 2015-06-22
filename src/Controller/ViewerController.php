<?php

namespace RamlServer\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewerController
{
    public function indexAction(Application $app, Request $request)
    {
        $filename = __DIR__ . '/../../example/world-music.raml';
        $parser = new \Raml\Parser();
        $api = $parser->parse($filename);
        //print_r($api); exit();

        return new Response($app['twig']->render(
            'index.html.twig',
            array('api' => $api)
        ));
    }
}
