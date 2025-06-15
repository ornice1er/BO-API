<?php

namespace App\Console\Commands;

use App\Models\ArchivedLog;
use App\Models\Log;
use File;
use Illuminate\Console\Command;
use Storage;

class LogFileGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fct:log-file-generator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $checked = ArchivedLog::first();
        $checked != null ? $startDate = $checked->created_at : $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        $logs = Log::whereBetween('created_at', [date_create($startDate), date_create($endDate)])->get();

        $path = 'log_'.date('d_m_Y').'.txt';
        if (! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put($path, '');
        }

        foreach ($logs as $value) {

            File::append($path, json_encode($value));
            $value->delete();
        }

        ArchivedLog::create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'file_path' => $path,
        ]);

    }
}
