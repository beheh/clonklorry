<?php

namespace Lorry\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Lorry\Environment;

class UserActivate extends Command {

	protected function configure() {
		$this
				->setName('user:activate')
				->setDescription('Activate a user account')
				->addArgument(
						'username', InputArgument::REQUIRED, 'The username of the account to activate'
				)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$lorry = new Environment();
		$lorry->setup();

		$username = $input->getArgument('username');
		$user = ModelFactory::build('User')->byUsername($username, true);
		if($user === null) {
			throw new \RuntimeException('Couldn\'t find user with username "'.$username.'".');
		}
		$user->activate();
		if($user->save()) {
			$output->writeln('<info>'.$user->getUsername().'\'s account is now activated</info>');
		}
		else {
			$output->writeln('<error>Error saving the user</error>');
		}
	}

}
