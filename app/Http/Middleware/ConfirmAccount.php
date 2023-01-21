<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ConfirmAccount extends BaseController
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()->verifyCode != null)  return $next($request);
        return $this->sendError(__('unauthorised'), ['status' => 0], 403);
    }
}
