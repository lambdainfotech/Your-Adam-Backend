<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\AuthorizesWithPermissions;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesWithPermissions;
}
