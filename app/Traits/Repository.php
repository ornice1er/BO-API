<?php

namespace App\Traits;

trait Repository
{
    /**
     * When the called method doesn't exists on the Repository,
     * Call it on the model
     */
    public function __call($method, $parameters)
    {
        return $this->model->$method(...$parameters);
    }
}
