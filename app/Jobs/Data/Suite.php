<?php

namespace App\Jobs\Data;

use App\Containers\Record;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class Suite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    protected $record;

    public function __construct(Record $record)
    {
        $this->record = $record;
    }

    public function handle()
    {
        $this->record->execute();
    }
}
