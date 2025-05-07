<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Log de l'état d'authentification
        file_put_contents(storage_path('logs/auth.log'), 
            date('Y-m-d H:i:s') . " - Middleware Authenticate - Auth::check(): " . (Auth::check() ? 'true' : 'false') . "\n", 
            FILE_APPEND);

        if (!Auth::check()) {
            file_put_contents(storage_path('logs/auth.log'), 
                date('Y-m-d H:i:s') . " - Middleware Authenticate - Utilisateur non authentifié\n", 
                FILE_APPEND);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
            return redirect()->route('login');
        }

        if (!session()->has('pharmacie_id')) {
            file_put_contents(storage_path('logs/auth.log'), 
                date('Y-m-d H:i:s') . " - Middleware Authenticate - Session pharmacie_id manquante\n", 
                FILE_APPEND);

            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }

        file_put_contents(storage_path('logs/auth.log'), 
            date('Y-m-d H:i:s') . " - Middleware Authenticate - Accès autorisé\n", 
            FILE_APPEND);

        return $next($request);
    }
} 