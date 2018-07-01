<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MeteoController extends Controller
{
    /**
     * @Route("/meteo", name="meteo")
     */
    public function index()
    {
        return $this->render('meteo/index.html.twig', [
            'controller_name' => 'MeteoController',
        ]);
    }
}
