<?php

namespace Lorry\Exception;

interface Exception
{
    public function getPresenter();
    public function getApiType();
    public function getHttpCode();
    public function getHttpMessage();
}
