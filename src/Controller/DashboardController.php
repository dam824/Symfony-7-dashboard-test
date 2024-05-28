<?php

namespace App\Controller;

use App\Service\BitcoinService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(BitcoinService $bitcoinService): Response
    {
        // Vérifie que l'utilisateur est authentifié
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Récupérer les données du Bitcoin
        $bitcoinData = $bitcoinService->getBitcoinHistoricalData();

        // Extraire les labels et les prix des données
        $bitcoinLabels = array_keys($bitcoinData);
        $bitcoinPrices = array_values($bitcoinData);

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'bitcoinLabels' => $bitcoinLabels,
            'bitcoinPrices' => $bitcoinPrices,
        ]);
    }
}
