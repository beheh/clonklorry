<?php

namespace Lorry\Validator;

use Lorry\Exception\ValidationException;
use \InvalidArgumentException;

abstract class AbstractValidator implements Validator
{

    abstract function performValidation($entity);
    protected $fails = array();

    public function fail($message)
    {
        if (!is_string($message)) {
            throw new InvalidArgumentException('message must be a string');
        }
        $this->fails[] = $message;
    }

    public function clear()
    {
        $this->fails = array();
    }

    /**
     *
     * @param mixed $entity
     * @throws ValidationException
     */
    public function validate($entity)
    {
        $previousFails = $this->fails;
        $this->fails = array();
        $this->performValidation($entity); // actual validation
        $this->fails = array_merge($this->fails, $previousFails); // ensure that custom messages are shown last
        if (!empty($this->fails)) {
            $exception = new ValidationException();
            $exception->setFails($this->fails);
            throw $exception;
        }
    }

    protected function validateNumber($number, $minimum, $maximum, $messages)
    {
        if ($minimum !== null && (!is_int($minimum)) || ($maximum !== null && !is_int($maximum))) {
            throw new InvalidArgumentException('minimum and maximum must be valid integers');
        }
        if (!is_int($number)) {
            $message = isset($messages['noInt']) ? $messages['noInt'] : $messages;
            $this->fail($message);
        } else {
            if ($minimum !== null && $number < $minimum) {
                $message = isset($messages['tooSmall']) ? $messages['tooSmall'] : $messages;
                $this->fail($message);
            }
            if ($maximum !== null && $number > $maximum) {
                $message = isset($messages['tooLarge']) ? $messages['tooLarge'] : $messages;
                $this->fail($message);
            }
        }
    }

    protected function validateStringLength($string, $minLength, $maxLength, $messages)
    {
        if (!is_int($minLength) || !($minLength >= 0) || !is_int($maxLength) || !($minLength >= 0)) {
            throw new InvalidArgumentException('minLength and maxLength must be valid integers greater or equal than zero');
        }
        $length = strlen($string);
        if ($length < $minLength) {
            $message = isset($messages['tooShort']) ? $messages['tooShort'] : $messages;
            $this->fail($message);
        }
        if ($length > $maxLength) {
            $message = isset($messages['tooLong']) ? $messages['tooLong'] : $messages;
            $this->fail($message);
        }
    }

    protected function validateStringPattern($string, $format, $message)
    {
        if (!strlen($format) || !(filter_var(FILTER_VALIDATE_REGEXP))) {
            throw new InvalidArgumentException('format must be a valid regular expression');
        }
        if (!preg_match($format, $string)) {
            $this->fail($message);
        }
    }

    protected function validateEmailAddress($emailAddress, $message)
    {
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $this->fail($message);
        }
    }
}
