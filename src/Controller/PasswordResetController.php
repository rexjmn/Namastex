<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordResetForm;
use App\Form\PasswordResetRequestForm;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class PasswordResetController extends AbstractController
{
    #[Route('/password/reset/request', name: 'app_password_reset_request')]
    public function request(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        $form = $this->createForm(PasswordResetRequestForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un token de reset
                $resetToken = Uuid::v4()->toRfc4122();
                $user->setResetPasswordToken($resetToken);
                $user->setResetPasswordRequestedAt(new \DateTimeImmutable());
                
                $entityManager->persist($user);
                $entityManager->flush();

                // Générer le lien de reset
                $resetLink = $this->generateUrl(
                    'app_password_reset',
                    ['token' => $resetToken],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                // Envoyer l'email
                $emailService->sendEmail(
                    'rexmayorga97@gmail.com',
                    $user->getEmail(),
                    'Réinitialisation de votre mot de passe',
                    'Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href="'.$resetLink.'">Réinitialiser mon mot de passe</a><br><br>Ce lien expire dans 1 heure.'
                );
            }

            // Même message pour éviter l'énumération d'utilisateurs
            $this->addFlash('success', 'Si votre adresse email existe dans notre base de données, vous allez recevoir un email avec les instructions pour réinitialiser votre mot de passe.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('password_reset/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/password/reset/{token}', name: 'app_password_reset')]
    public function reset(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->findOneBy(['resetPasswordToken' => $token]);

        if (!$user || $user->isResetPasswordRequestExpired()) {
            $this->addFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
            return $this->redirectToRoute('app_password_reset_request');
        }

        $form = $this->createForm(PasswordResetForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            
            // Hasher le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            
            // Nettoyer les tokens de reset
            $user->setResetPasswordToken(null);
            $user->setResetPasswordRequestedAt(null);
            
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('password_reset/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}