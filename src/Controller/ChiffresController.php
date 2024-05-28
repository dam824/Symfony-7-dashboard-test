<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChiffresController extends AbstractController
{
    #[Route('/chiffres', name: 'app_chiffres')]
    public function index(): Response
    {
        return $this->render('chiffres/index.html.twig', [
            'controller_name' => 'ChiffresController',
        ]);
    }
}
