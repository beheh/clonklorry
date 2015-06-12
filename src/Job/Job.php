<?php

namespace Lorry\Job\Job;

interface Job
{
    public function getQueue();
    public function perform();
}
