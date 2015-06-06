<?php

namespace Lorry\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Lorry\Model\User;

class UserAdmin extends UserModifyCommand
{

    protected function configure()
    {
        $this
            ->setName('user:admin')
            ->setDescription('Make a user administrator')
            ->addArgument(
                'username', InputArgument::REQUIRED, 'The username of the new administrator'
            )
        ;
    }

    function modifyUser(User $user, InputInterface $input, OutputInterface $output)
    {
        $user->setPermission(User::PERMISSION_ADMINISTRATE);
    }

    function checkResult(User $user, InputInterface $input, OutputInterface $output)
    {
        if ($user->isAdministrator()) {
            $output->writeln('<info>'.$user->getUsername().' is now an administrator</info>');
        } else {
            $output->writeln('<error>Failed to make '.$user->getUsername().' an administrator</error>');
        }
    }
}
