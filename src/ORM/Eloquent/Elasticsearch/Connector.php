<?php

namespace Idunis\EventSauce\ORM\Eloquent\Elasticsearch;

use Idunis\EventSauce\ORM\Eloquent\Elasticsearch\Manager;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

trait Connector
{
    private $indexOnQueue = false;

    /**
     * Set Connection Name
     *
     * @return string
     */
    public function setIndexConnection($connection)
    {
        $this->indexConnection = $connection;
    }

    /**
     * Get Connection Name
     *
     * @return string
     */
    public function getIndexConnection()
    {
        return $this->indexConnection ?? null;
    }

    public function onQueue($flag = true)
    {
        $this->indexOnQueue = !!$flag;
        return $this;
    }

    public function getElasticsearchClient()
    {
        return app(Manager::class)->connection($this->getIndexConnection());
    }
}