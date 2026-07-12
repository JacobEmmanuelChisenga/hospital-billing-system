<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict a route to one or more staff roles.
 *
 * Usage on routes: ->middleware('role:administrator') or ->middleware('role:accounts,nursing')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowedRoles = array_map(
            fn (string $role) => UserRole::fromRouteParameter($role),
            $this->normalizeRoleParameters($roles)
        );

        if (! $user->hasAnyRole($allowedRoles)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }

    /**
     * Laravel passes each role separately, but cached routes may still include
     * legacy comma-separated values — normalize both shapes safely.
     *
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    private function normalizeRoleParameters(array $roles): array
    {
        $normalized = [];

        foreach ($roles as $role) {
            foreach (explode(',', $role) as $part) {
                $part = trim($part);

                if ($part !== '') {
                    $normalized[] = $part;
                }
            }
        }

        return $normalized;
    }
}
