<?php

namespace App\Models;

use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Spatie\Permission\Models\Permission as SpatiePermission;

final class Permission extends SpatiePermission
{
    use Filterable;

    protected $guarded = [];
}
