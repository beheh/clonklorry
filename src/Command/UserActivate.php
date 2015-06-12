<?php

namespace Lorry\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Lorry\Model\User;

class UserActivate extends UserModifyCommand
{

    protected function configure()
    {
        $this
            ->setName('user:activate')
            ->setDescription('Activate a user account')
            ->addArgument(
                'username', InputArgument::REQUIRED, 'The username of the account to activate'
            )
        ;
    }

    protected function modifyUser(User $user, InputInterface $input, OutputInterface $output)
    {
        $user->activate();
    }

    protected function checkResult(User $user, InputInterface $input, OutputInterface $output)
    {
        if($user->isActivated()) {
            $output->writeln('<info>'.$user->getUsername().'\'s account is now activated</info>');
        }
        else {
            $output->writeln('<error>Failed to activate '.$user->getUsername().'\'s account</error>');
        }
    }
}
