<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 */
class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'address'];

    /**
     * Managers that belong to this store.
     *
     * @return HasMany<User, $this>
     */
    public function managers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', Role::Manager);
    }

    /**
     * Trainees that belong to this store.
     *
     * @return HasMany<Trainee, $this>
     */
    public function trainees(): HasMany
    {
        return $this->hasMany(Trainee::class);
    }
}
