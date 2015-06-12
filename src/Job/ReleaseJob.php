<?php

namespace Lorry\Job;

use Lorry\Job;

class ReleaseJob extends AbstractJob
{

    public function getQueue()
    {
        return 'release';
    }

    public function execute()
    {
        $release = $this->persistence->build('Release')->byId(1);
        //$this->files->transfer('addon1/release1/ModernCombat.c4d');
        $release->setShipping(false);
        $release->save();
    }
}
