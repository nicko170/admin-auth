<?php

namespace Brackets\AdminAuth\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ParentHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Handler extends ParentHandler
{
    /**
     * Convert an authentication exception into a response.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $url = route('brackets/admin-auth::admin/login');
       
        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest($url);
    }
}
