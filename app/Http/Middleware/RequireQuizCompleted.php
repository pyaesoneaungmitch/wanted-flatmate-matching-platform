<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequireQuizCompleted
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login.form');

        $hasQuiz = DB::table('quiz_responses')
            ->where('user_id', $user->user_id)
            ->exists();

        if (!$hasQuiz && !$request->is('quiz*')) {
            return redirect()->route('quiz.show');
        }

        return $next($request);
    }
}