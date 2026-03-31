<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Models\Role;
use App\Modules\Core\Abstracts\BaseRepository;

class RoleRepository extends BaseRepository
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    protected function getCachePrefix(): string
    {
        return 'roles';
    }

    public function findBySlug(string $slug): ?Role
    {
        return $this->findBy(['slug' => $slug]);
    }

    public function getDefaultRole(): Role
    {
        $role = $this->findBySlug('customer');

        if (!$role) {
            throw new \RuntimeException('Default customer role not found.');
        }

        return $role;
    }
}
