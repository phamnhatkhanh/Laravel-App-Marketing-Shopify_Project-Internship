<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobBatch extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_batches';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options'      => 'collection',
        'failed_jobs'  => 'integer',
        'created_at'   => 'datetime',
        'cancelled_at' => 'datetime',
        'finished_at'  => 'datetime',
    ];

    /**
     * Get the number of jobs executed.
     *
     * @return int
     */
    public function processedJobs()
    {
        return $this->total_jobs - $this->pending_jobs;
    }

    /**
     * Get the percentage of jobs that have been processed (between 0-100).
     *
     * @return int
     */
    public function progress(): int
    {
        return $this->total_jobs > 0 ? round(($this->processedJobs() / $this->total_jobs) * 100) : 0;
    }

    /**
     * Determine if the batch has pending jobs
     *
     * @return bool
     */
    public function hasPendingJobs(): bool
    {
        return $this->pending_jobs > 0;
    }

    /**
     * Determine if the batch has job failures.
     *
     * @return bool
     */
    public function hasFailures(): bool
    {
        return $this->failed_jobs > 0;
    }

    /**
     * Determine if the batch has finished executing.
     *
     * @return bool
     */
    public function finished(): bool
    {
        return !is_null($this->finished_at);
    }

    /**
     * Determine if all jobs failed.
     *
     * @return bool
     */
    public function failed(): bool
    {
        return $this->failed_jobs === $this->total_jobs;
    }

    /**
     * Determine if the batch has been canceled.
     *
     * @return bool
     */
    public function cancelled(): bool
    {
        return !is_null($this->cancelled_at);
    }
}
