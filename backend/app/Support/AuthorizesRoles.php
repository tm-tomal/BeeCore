<?php

namespace App\Support;

trait AuthorizesRoles
{
    protected function authorizeRoles(string ...$roles): void
    {
        abort_unless(in_array(auth()->user()?->role, $roles, true), 403);
    }
}