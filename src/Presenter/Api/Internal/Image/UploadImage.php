<?php

namespace Lorry\Presenter\Api\Internal\Image;

use Lorry\Presenter\Api\Presenter;

class UploadImage extends Presenter
{

    public function get()
    {
        $this->security->requireAdministrator();
        $this->security->requireValidState();

        $this->file->queryFile('images');

        $this->display(array('chuck' => 'exists'));
    }

    public function post()
    {
        $this->security->requireAdministrator();
        $this->security->requireValidState();

        $this->file->putFile('images');

        $this->display(array('chuck' => 'exists'));
    }

}
