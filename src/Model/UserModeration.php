<?php

namespace Lorry\Model;

use Lorry\Model;

class UserModeration extends Model
{

    public function getTable()
    {
        return 'user_moderation';
    }

    public function getSchema()
    {
        return array(
            'user' => 'int',
            'action' => 'string',
            'from' => 'string',
            'to' => 'string',
            'executor' => 'int',
            'timestamp' => 'datetime');
    }

    public function onInsert()
    {
        $this->setValue('timestamp', time());
    }

    public function byUser($user)
    {
        return $this->all()->byValue('user', $user);
    }

    public function setUser($user)
    {
        return $this->setValue('user', $user);
    }

    public function getUser()
    {
        return $this->getValue('user');
    }

    public function fetchUser() {
        return $this->fetch('User', 'user');
    }

    public function setAction($action)
    {
        return $this->setValue('action', $action);
    }

    public function getAction()
    {
        return $this->getValue('action');
    }

    public function setFrom($from)
    {
        return $this->setValue('from', $from);
    }

    public function getFrom()
    {
        return $this->getValue('from');
    }

    public function setTo($to)
    {
        return $this->setValue('to', $to);
    }

    public function getTo()
    {
        return $this->getValue('to');
    }

    public function setExecutor($executor) {
        return $this->setValue('executor', $executor);
    }

    public function getExecutor() {
        return $this->getValue('executor');
    }

    public function fetchExecutor() {
        return $this->fetch('User', 'executor');
    }

    public function getTimestamp()
    {
        return $this->getValue('timestamp');
    }

    public function forPresenter($dateFormat = null) {
        $result = array();
        $result['user'] = $this->fetchUser()->forPresenter();
        $result['action'] = $this->getAction();
        $result['from'] = $this->getFrom();
        $result['to'] = $this->getTo();
        if ($dateFormat !== null) {
            $result['timestamp'] = date($dateFormat, $this->getTimestamp());
        }
        $executor = $this->fetchExecutor();
        if($executor) {
            $result['executor'] = $executor->forPresenter();
        }
        return $result;
    }

}
