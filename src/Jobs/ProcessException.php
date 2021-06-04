<?php

namespace LaraBug\Jobs;


use LaraBug\Models\Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessException implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $project;
    public $data;
    public $date;

    /**
     * Create a new job instance.
     *
     * @param array $data
     * @param \App\Models\Logging\Project $project
     * @param \Illuminate\Support\Carbon $date
     */
    public function __construct(array $data, $date = null)
    {
        $this->data = $data;
        $this->date = $date ? $date : now();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!is_array($this->data)) {
            return;
        }

        try {
            $exception = Exception::create($this->data);

            $exception->created_at = $this->date;
            $exception->save();
        } catch (\Exception $exception) {
        }
    }
}
