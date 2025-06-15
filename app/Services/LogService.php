<?php

namespace App\Services;

use App\Http\Repositories\LogRepository;

class LogService
{
    /**
     * The Log repository being queried.
     *
     * @var LogRepository
     */
    protected $logRepository;

    public function __construct(logRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    public function trace($data)
    {
        $data['origin'] = 'AUTH';
        $this->logRepository->makeStore($data);
    }
}
