<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class RegistrationController extends AbstractController
{

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $token = Uuid::v4()->toRfc4122();
            $user->setToken($token);
            $user->setIsVerified(false);
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            $confirmationLink = $this->generateUrl(
                'app_email_confirmation',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $emailService->sendEmail(
                'rexmayorga97@gmail.com',
                $user->getEmail(),
                'Confirmation Email',
                'Merci de confirmer votre adresse : <a href="' . $confirmationLink . '">Confirmer mon email</a>'
            );


            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
