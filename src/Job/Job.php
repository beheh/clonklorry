<?php

namespace Lorry\Job;

interface Job
{
    public function getQueue();
    public function perform();
}
