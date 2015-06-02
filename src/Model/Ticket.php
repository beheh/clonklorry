<?php

namespace Lorry\Model;

use Lorry\Model;

class Ticket extends Model
{

    public function getTable()
    {
        return 'ticket';
    }

    public function getSchema()
    {
        return array(
            'message' => 'text',
            'hash' => 'varchar',
            'user' => 'int',
            'submitted' => 'datetime',
            'escalated' => 'datetime',
            'staff' => 'int',
            'acknowledged' => 'datetime'
        );
    }

    protected function onInsert()
    {
        $this->setValue('submitted', time());
    }

    public function byNew()
    {
        $constraints = array();
        $constraints['acknowledged'] = null;
        $constraints['escalated'] = null;
        $this->all()->order('submitted');
        return $this->byValues($constraints);
    }

    public function setMessage($message)
    {
        $this->validateString($message, 10, 2048);
        $this->setValue('message', $message);
        $this->setValue('hash', sha1($message));
    }

    public function getMessage()
    {
        return $this->getValue('message');
    }

    public function getHash()
    {
        return $this->getValue('hash');
    }

    public function byHash($hash)
    {
        return $this->byValue('hash', $hash);
    }

    public function setUser($user)
    {
        $this->setValue('user', $user);
    }

    public function getUser()
    {
        return $this->getValue('user');
    }

    public function fetchUser()
    {
        return $this->fetch('User', 'user');
    }

    public function getSubmitted()
    {
        return $this->getValue('submitted');
    }

    public function escalate()
    {
        $this->setValue('escalated', time());
    }

    public function isEscalated()
    {
        return $this->getValue('escalated') !== null;
    }

    public function acknowledge()
    {
        $this->setValue('acknowledged', time());
    }

    public function isAcknowledged()
    {
        return $this->getValue('acknowledged') !== null;
    }

    public function dispute()
    {
        $this->setValue('acknowledged', null);
    }

    public function setStaff($staff)
    {
        $this->setValue('staff', $staff);
    }

    public function getStaff()
    {
        return $this->getValue('staff');
    }

    public function fetchStaff()
    {
        return $this->fetch('User', 'staff');
    }

    public function forPresenter($dateFormat = null)
    {
        $result = array('id' => $this->getId(),
            'message' => $this->getMessage()
        );
        if ($dateFormat !== null) {
            $result['submitted'] = date($dateFormat, $this->getSubmitted());
        }
        $user = $this->fetchUser();
        if ($user) {
            $result['user'] = $user->forPresenter();
        }
        return $result;
    }
}
