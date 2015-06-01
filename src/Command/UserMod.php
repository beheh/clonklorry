<?php

namespace Lorry\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Lorry\Model\User;

class UserMod extends UserModifyCommand
{

    protected function configure()
    {
        $this
            ->setName('user:mod')
            ->setDescription('Make a user moderator')
            ->addArgument(
                'username', InputArgument::REQUIRED,
                'The username of the new moderator'
            )
        ;
    }

    function modifyUser(User $user, InputInterface $input,
        OutputInterface $output)
    {
        $user->setPermission(User::PERMISSION_MODERATE);
        $output->writeln('<info>'.$user->getUsername().' is now a moderator</info>');
    }
}
