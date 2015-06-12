<?php

namespace Lorry\Presenter;

use Symfony\Component\HttpFoundation\Request;

interface Presenter
{

    public function setRequest(Request $request);

    public function handle($method, $parameters);
}
