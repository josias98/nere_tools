<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessTool
{
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        abort_unless($request->user()?->canAccessTool($slug), 403, "Vous n'avez pas acces a cette application.");

        return $next($request);
    }
}
