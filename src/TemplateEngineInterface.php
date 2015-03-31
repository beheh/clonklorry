<?php

namespace Lorry;

interface TemplateEngineInterface
{

    public function addGlobal($name, $value);

    public function loadTemplate($name, $index = null);

    public function clearCacheFiles();
}
