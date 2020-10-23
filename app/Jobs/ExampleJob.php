<?php

namespace App\Jobs;
use App\Models\Log;
use Illuminate\Http\Request;

class ExampleJob extends Job
{
	public $queue;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->queue = $data['queue'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		Log::create([
            'instance'      => '',
            'channel'       => '',
            'message'       => '',
            'level'         => '',
            'ip'            => '-',
            'user_agent'    => '-',
            'url'           => '-',
            'context'       => '-',
            'extra'         => $this->queue

        ]);
    }
}
