<?php

namespace Lorry\Model;

use Lorry\Model;

class Banner extends Model
{

    public function getTable()
    {
        return 'banner';
    }

    public function getSchema() {
        return array(
            'title' => 'text',
            'description' => 'text',
            'addon' => 'int',
            'release' => 'int'
        );
    }

}