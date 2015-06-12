<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Model\Addon;
use Lorry\Model\AddonTranslation;
use Lorry\Validator\AddonValidator;
use Lorry\Validator\AddonTranslationValidator;
use Lorry\Exception\ValidationException;

class Developers extends AbstractPresenter
{

    public function get()
    {
        $this->context['games'] = $this->manager->getRepository('Lorry\Model\Game')->findAll();

        if (!isset($this->context['selected_game'])) {
            $this->context['selected_game'] = filter_input(INPUT_GET, 'create');
        }

        if (isset($_GET['create']) && !isset($this->context['focus_title'])) {
            $this->context['focus_title'] = true;
        }

        /* random name generation */

        $singular_objects = array(gettext('Wipf'), gettext('Monster'), gettext('Bridge'),
            gettext('Tower'), gettext('Stage'), gettext('Pressurewave'), gettext('Fantasy'),
            gettext('Stippel'), gettext('Quake'), gettext('Zap'));
        $singular_phrases = array(gettext('%s pack'), gettext('%s of despair'), gettext('%s Infinity'),
            gettext('%sarena'), gettext('%s fight'), gettext('%s race'), gettext('%s Party'),
            gettext('%s lands'), gettext('%s defense'));
        $plural_objects = array(gettext('Wipfs'), gettext('Monsters'), gettext('Bridges'),
            gettext('Towers'), gettext('Pressurewaves'), gettext('Flints'), gettext('Knights'),
            gettext('Clonks'), gettext('Stippels'), gettext('Fish'));
        $plural_phrases = array(gettext('Metal & %s'), gettext('Left 2 %s'), gettext('%s descend from the top'),
            gettext('%s rise from the bottom'));

        if (!rand(0, 5)) {
            $example = sprintf($plural_phrases[array_rand($plural_phrases)], $plural_objects[array_rand($plural_objects)]);
        } else {
            $example = sprintf($singular_phrases[array_rand($singular_phrases)], $singular_objects[array_rand($singular_objects)]);
        }

        $modifiers = array(gettext('%s Extreme'), gettext('Codename: %s'), gettext('%s Remake'),
            gettext('%s Reloaded'));
        if (!rand(0, 5)) {
            $example = sprintf($modifiers[array_rand($modifiers)], $example);
        }

        $this->context['exampletitle'] = $example;

        /* end random name generation */

        $this->display('publish/developers.twig');
    }

    public function post()
    {
        $this->security->requireLogin();
        $this->security->requireValidState();

        $user = $this->session->getUser();

        $addonRepository = $this->manager->getRepository('Lorry\Model\Addon');
        $addonValidator = new AddonValidator();
        $translationValidator = new AddonTranslationValidator();

        $addon = new Addon();
        $translation = new AddonTranslation();

        $addon->setOwner($user);

        $title = trim(filter_input(INPUT_POST, 'title'));

        $this->context['addontitle'] = $title;

        $translation->setTitle($title);
        $translation->setLanguage($this->localisation->getDisplayLanguage());

        $game = $this->manager->getRepository('Lorry\Model\Game')->findOneBy(array('short' => filter_input(INPUT_POST, 'game')));
        if (!$game) {
            $addonValidator->fail(gettext('Invalid game.'));
        }
        $this->context['selected_game'] = $game->getShort();
        $addon->setGame($game);

        if($addonRepository->getOwnedByTitleAndGame($user, $title, $game)) {
            $addonValidator->fail(gettext('You have already created an addon with this title.'));
        }

        try {
            $translationValidator->validate($translation);
            $addon->setDefaultTranslation($translation);
            $addon->addTranslation($translation);
            $addonValidator->validate($addon);
            $this->manager->persist($addon);
            $this->manager->flush();
            $this->redirect('/publish?created='.$addon->getId());
            return;
        } catch (ValidationException $ex) {
            $this->error('creation', implode('<br>', $ex->getFails()));
        }

        $this->get();
    }
}
