<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-super-admin',
    description: 'Crea un usuario super administrador',
)]
class CreateSuperAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email del super administrador')
            ->addArgument('username', InputArgument::REQUIRED, 'Nombre de usuario')
            ->addArgument('password', InputArgument::REQUIRED, 'Contraseña');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $io->note(sprintf('On est en train de créer un super administrateur: %s', $email));

        // Verificar si el usuario ya existe
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if ($existingUser) {
            $user = $existingUser;
            $io->note('Le super administrateur existe déjà. On va mettre à jour ses informations.');
        } else {
            $user = new User();
            $user->setEmail($email);
            $user->setUsername($username);
        }

        // Establecer la contraseña hasheada
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Asignar el rol de super administrador
        $user->setRoles(['ROLE_SUPER_ADMIN']);

        if (!$existingUser) {
            $this->entityManager->persist($user);
        }
        
        $this->entityManager->flush();

        $io->success('Super administrateur crée correctamente.');

        return Command::SUCCESS;
    }
}