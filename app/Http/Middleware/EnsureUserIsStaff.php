<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_staff) {
            abort(403, 'Только для сотрудников.');
        }

        return $next($request);
    }
}
