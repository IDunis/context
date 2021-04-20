<?php

namespace Idunis\EventSauce\Support\Facades;;

use Illuminate\Support\Facades\Facade;

class Elasticsearch extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'eloquent.elasticsearch';
    }
}