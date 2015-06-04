<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Portal extends Presenter
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

        $this->context['addons'] = $user->getOwnedAddons();

        $this->display('publish/portal.twig');
    }
}
