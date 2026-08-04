<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByGoogleId(string $googleId): ?User;

    /**
     * @param  array{name: string, email: string, password?: string|null, google_id?: string|null, avatar?: string|null}  $data
     */
    public function create(array $data): User;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User;

    public function delete(User $user): bool;

    /**
     * @param  array{search?: string|null, role?: string|null, per_page?: int}  $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function getPaginated(array $filters = []): LengthAwarePaginator;
}
