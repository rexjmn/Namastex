<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class UserAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_users_index')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user_admin/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}/toggle-admin', name: 'app_admin_users_toggle_admin')]
    public function toggleAdmin(User $user, EntityManagerInterface $entityManager): Response
    {
        // No permitir que un usuario se cambie sus propios roles
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'No puede cambiar sus propios roles.');
            return $this->redirectToRoute('app_admin_users_index');
        }

        if ($user->hasRole('ROLE_ADMIN')) {
            $user->setRoles(array_diff($user->getRoles(), ['ROLE_ADMIN']));
            $message = 'Rol de administrador eliminado.';
        } else {
            $user->addRole('ROLE_ADMIN');
            $message = 'Rol de administrador añadido.';
        }

        $entityManager->flush();
        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_admin_users_index');
    }

    #[Route('/{id}/toggle-super-admin', name: 'app_admin_users_toggle_super_admin')]
    public function toggleSuperAdmin(User $user, EntityManagerInterface $entityManager): Response
    {
        // No permitir que un usuario se cambie sus propios roles
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'No puede cambiar sus propios roles.');
            return $this->redirectToRoute('app_admin_users_index');
        }

        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            $user->setRoles(array_diff($user->getRoles(), ['ROLE_SUPER_ADMIN']));
            $message = 'Rol de super administrador eliminado.';
        } else {
            $user->addRole('ROLE_SUPER_ADMIN');
            $message = 'Rol de super administrador añadido.';
        }

        $entityManager->flush();
        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_admin_users_index');
    }
}