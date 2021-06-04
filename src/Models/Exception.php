<?php

namespace LaraBug\Models;

use Kblais\Uuid\Uuid;
use DateTimeInterface;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\ExceptionWasCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property mixed exception
 * @property mixed class
 * @property \Carbon\Carbon created_at
 * @property mixed file
 * @property mixed line
 * @property string status
 * @property bool mailed
 * @property string publish_hash
 * @property null published_at
 */
class Exception extends Model
{
    use Uuid,
        Filterable,
        HasFactory;

    /**
     * Status vars
     */
    const OPEN = 'OPEN';
    const READ = 'READ';
    const FIXED = 'FIXED';
    const DONE = 'DONE';

    /**
     * @var array
     */
    protected $guarded = [
        'created_at',
        'updated_at',
        'published_at'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'user' => 'array',
        'storage' => 'array',
        'executor' => 'array',
        'mailed' => 'boolean',
        'additional' => 'array'
    ];

    protected $appends = [
        'human_date',
        'public_route_url',
        'route_url',
        'status_text',
        'short_exception_text',
        'executor_output',
        'markup_language'
    ];

    public function getHumanDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getPublicRouteUrlAttribute(): ?string
    {
        if (! $this->publish_hash) {
            return null;
        }

        return route('public.exception', $this);
    }

    public function getStatusTextAttribute()
    {
        return trans('status.' . strtoupper($this->status));
    }

    public function getMarkupLanguageAttribute()
    {
        $pathinfo = pathinfo($this->file);

        $extension = array_get($pathinfo, 'extension');

        if ($extension == 'php') {
            return 'language-php';
        }

        if ($extension == 'html') {
            return 'language-html';
        }

        // Default to php..
        return 'language-php';
    }

    public function getExecutorOutputAttribute()
    {
        // strip all the html on the line, replace the line number
        // with nothing(the frontend will do the line numbers for us)
        // and output as one giant string
        return join(PHP_EOL, collect($this->executor)->map(function ($line) {
            return preg_replace(
                "/^[0-9]{1,}[.]/i",
                "",
                str_replace(
                    "\n",
                    "",
                    array_get($line, 'line')
                )
            );
        })->toArray());
    }

    public function getShortExceptionTextAttribute()
    {
        if (!$this->exception) {
            return '-No exception text-';
        }

        return str_limit($this->exception, 75);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeNotMailed($query)
    {
        return $query->whereMailed(false);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeNew($query)
    {
        return $query->whereStatus(self::OPEN);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     *
     */
    public function markAsRead()
    {
        $this->status = self::READ;
        $this->save();
    }

    /**
     * @param string $status
     */
    public function markAs($status = self::FIXED)
    {
        $this->status = $status;
        $this->save();

        if ($status == self::FIXED) {
            Statistic::incrementStatistics('total_fixed_exceptions');
        }
    }

    /**
     * Mark as e-mailed, whenever this status is true it will not be e-mailed out to the user.
     */
    public function markAsMailed()
    {
        $this->mailed = true;
        $this->save();
    }

    public function isMarkedAsMailed()
    {
        return $this->mailed;
    }

    public function makePublic()
    {
        $this->publish_hash = str_random(15);
        $this->published_at = carbon();
        $this->save();

        return $this;
    }

    public function removePublic()
    {
        $this->publish_hash = null;
        $this->published_at = null;
        $this->save();

        return $this;
    }

    public function isPublic()
    {
        return $this->publish_hash ? true : false;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($exception) {
            $exception->status = self::OPEN;
        });
    }
}
