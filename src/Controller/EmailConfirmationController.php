<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EmailConfirmationController extends AbstractController
{
    #[Route('/email/confirmation/{token}', name: 'app_email_confirmation')]
    public function index(string $token, UserRepository $repo, EntityManagerInterface $em): Response
    {
        // Rechercher l'utilisateur par token
        $user = $repo->findOneBy(['token' => $token]);
        
        if (!$user) {
            $this->addFlash('error', 'Token de confirmation invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur n'est pas déjà vérifié
        if ($user->isVerified()) {
            $this->addFlash('info', 'Votre compte est déjà confirmé.');
            return $this->redirectToRoute('app_login');
        }

        // Confirmer l'utilisateur
        $user->setToken(null);
        $user->setIsVerified(true);
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Votre compte a été confirmé avec succès ! Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }
    
    // Ruta alternativa para mostrar la página de confirmación
    #[Route('/email/confirmation', name: 'app_email_confirmation_page')]
    public function confirmationPage(): Response
    {
        return $this->render('email_confirmation/index.html.twig');
    }
}