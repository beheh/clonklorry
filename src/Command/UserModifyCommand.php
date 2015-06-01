<?php

namespace Lorry\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Lorry\Environment;
use Lorry\Model\User;

abstract class UserModifyCommand extends Command
{

    protected $manager;

    abstract function modifyUser(User $user, InputInterface $input, OutputInterface $output);

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lorry = new Environment();
        $lorry->setup();
        $this->manager = $lorry->getContainer()->get('manager');

        $username = $input->getArgument('username');
        $user = $this->manager->getRepository('Lorry\Model\User')->findOneBy(array('username' => $username));
        if ($user === null) {
            throw new \RuntimeException('Couldn\'t find user with username "'.$username.'".');
        }
        $this->modifyUser($user, $input, $output);
        $this->manager->flush();
    }
}
