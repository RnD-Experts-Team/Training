<?php

namespace App\Models;

use Database\Factories\TraineeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $store_id
 * @property string $name
 * @property string|null $position
 * @property Carbon|null $hired_at
 * @property int|null $created_by
 * @property-read Store $store
 * @property-read User|null $creator
 * @property-read Collection<int, User> $managers
 * @property-read Collection<int, Evaluation> $evaluations
 */
class Trainee extends Model
{
    /** @use HasFactory<TraineeFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['store_id', 'name', 'position', 'hired_at', 'created_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hired_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Managers assigned to evaluate this trainee.
     *
     * @return BelongsToMany<User, $this>
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manager_trainee');
    }

    /**
     * @return HasMany<Evaluation, $this>
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Limit the query to trainees the given user is allowed to see. Super admins
     * see everyone; a manager sees every trainee in any of their assigned stores,
     * plus any trainee explicitly assigned to them (the pivot is an additive
     * grant for cross-store cases). A manager with no stores sees only pivot links.
     *
     * @param  Builder<Trainee>  $query
     * @return Builder<Trainee>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $storeIds = $user->stores->pluck('id');

        return $query->where(function (Builder $q) use ($user, $storeIds): void {
            $q->whereHas('managers', fn (Builder $inner) => $inner->whereKey($user->id));

            if ($storeIds->isNotEmpty()) {
                $q->orWhereIn('store_id', $storeIds);
            }
        });
    }

    /**
     * Limit the query to a specific store (no-op when null).
     *
     * @param  Builder<Trainee>  $query
     * @return Builder<Trainee>
     */
    public function scopeInStore(Builder $query, ?int $storeId): Builder
    {
        return $query->when($storeId, fn (Builder $q) => $q->where('store_id', $storeId));
    }
}
