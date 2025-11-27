<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A simple Model wrapper for file system records to make them compatible with Filament Tables.
 * This class doesn't interact with the database.
 */
class FileRecord extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'path',
        'filename',
        'size',
        'modified',
        'type',
        'pages_count',
        'books_count',
        'thumbnail',
    ];

    /**
     * Create a new FileRecord instance from an array.
     *
     * @param array $attributes
     * @return static
     */
    public static function make(array $attributes): static
    {
        $instance = new static();
        $instance->fill($attributes);
        // Use MD5 hash of path as key to avoid special characters in JavaScript
        $key = isset($attributes['path']) ? md5($attributes['path']) : md5(json_encode($attributes));
        $instance->setAttribute($instance->getKeyName(), $key);
        $instance->exists = true;

        return $instance;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return 'file_records'; // Dummy table name
    }

    /**
     * Create a new Eloquent query builder for the model that won't hit the database.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new \Illuminate\Database\Eloquent\Builder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new \Illuminate\Database\Query\Builder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }
}
