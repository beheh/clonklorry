<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter\AbstractPresenter;

class Portal extends AbstractPresenter
{

    public function get()
    {
        if (!$this->session->authenticated()) {
            $this->display('publish/greeter.twig');
            return;
        }

        $this->security->requireLogin();
        $user = $this->session->getUser();

        if (isset($_GET['created'])) {
            $this->success('addons', gettext('Addon created.'));
        }

        $this->context['addons'] = $user->getOwnedAddons()->toArray();

        $this->display('publish/portal.twig');
    }
}
