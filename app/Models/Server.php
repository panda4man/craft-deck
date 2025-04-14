<?php

namespace App\Models;

use App\Enums\ServerTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Server extends Model
{
    protected $fillable = ['name', 'version', 'ram_mb', 'slug'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Server $server) {
            if (empty($server->slug)) {
                $server->slug = Str::slug($server->name);
            }

            if(empty($server->type)) {
                $server->type = ServerTypes::VANILLA;
            }

            if(empty($server->path)) {
                $server->path = sprintf("%s/%s", config('craft-deck.worlds_path'), $server->slug);
            }
        });
    }
}
