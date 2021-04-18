<?php

namespace Idunis\EventSauce\Exceptions;

use Exception;

class AggregateRootRepositoryInstanciationFailed extends Exception
{
    public static function aggregateRootClassDoesNotExist()
    {
        return new static('You have to set an aggregate root before the repository can be initialized.');
    }

    public static function aggregateRootClassIsNotValid()
    {
        return new static('Not a valid aggregate root class');
    }
}
