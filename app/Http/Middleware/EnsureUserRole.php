<?php

namespace App\Http\Middleware;

use App\Domain\Admin\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $targetRole = UserRole::tryFrom($role);

        if ($targetRole && $user->role !== $targetRole && $user->role !== UserRole::SUPER_ADMIN) {
            abort(403, 'Akses terbatas untuk peran ' . $targetRole->label());
        }

        return $next($request);
    }
}
