<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesAuthenticatedUser;

abstract class Controller
{
    use ResolvesAuthenticatedUser;
}
