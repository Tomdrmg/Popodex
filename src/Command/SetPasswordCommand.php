<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:set-password',
    description: 'Modifie le mot de passe d\'un utilisateur existant',
)]
class SetPasswordCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager, private UserPasswordHasherInterface $passwordHasher) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addArgument('new-password', InputArgument::REQUIRED, 'Nouveau mot de passe');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $newPassword = $input->getArgument('new-password');

        // Trouver l'utilisateur par email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln(sprintf('<error>Utilisateur non trouvé avec l\'email : %s</error>', $email));
            return Command::FAILURE;
        }

        // Hasher le nouveau mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->flush();

        $output->writeln(sprintf('<info>Mot de passe modifié pour l\'utilisateur : %s</info>', $email));

        return Command::SUCCESS;
    }
}
