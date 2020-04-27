<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Pjax extends \Encore\Admin\Middleware\Pjax
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($request->get('mt_pjax')) {
            try {
                $this->filterResponse($response, '#pjax-container')
                    ->setUriHeader($response, $request);
            } catch (\Exception $exception) {
            }
        }

        if ( ! $request->pjax() || $response->isRedirection() || Admin::guard()->guest()) {
            return $response;
        }

        if ( ! $response->isSuccessful()) {
            return $this->handleErrorResponse($response);
        }

        try {
            $this->filterResponse($response, $request->header('X-PJAX-CONTAINER'))
                ->setUriHeader($response, $request);
        } catch (\Exception $exception) {
        }

        return $response;
    }

}
