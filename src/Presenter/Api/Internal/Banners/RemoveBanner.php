<?php

namespace Lorry\Presenter\Api\Internal\Banner;

use Lorry\Presenter\Api\Presenter;

class RemoveBanner extends Presenter
{

    public function post($banner_id)
    {
        $this->error('banner_id: '.$banner_id);

        $this->security->requireAdministrator();
        $this->security->requireValidState();

        $banner = $this->manager->getRepository('Lorry\Model\Banner')->find($banner_id);

        if (!$banner) {
            $this->display(array('success' => true));
            return;
        }

        $this->manager->remove($banner);
        $this->manager->flush();

        $this->display(array('success' => true));
    }

}
