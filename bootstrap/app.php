<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'agent-code/search',
        ]);
        
        // Add visitor tracking middleware to web routes
        $middleware->web(append: [
            \App\Http\Middleware\TrackVisitor::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A CSRF token can still go stale in ways no server config prevents — the
        // customer leaves the tab open past the session lifetime, or signs out in
        // another tab. Send them back to the form with a readable message instead
        // of the raw "419 Page Expired" screen.
        // NB: Handler::prepareException() rewrites TokenMismatchException into a plain
        // HttpException(419) before render callbacks run, so this must match on the
        // HttpException — a callback typed against TokenMismatchException never fires.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $isCsrfFailure = $e->getStatusCode() === 419
                && $e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException;

            if (! $isCsrfFailure) {
                return null; // let Laravel render every other HTTP error as usual
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            return redirect()
                ->back(302, [], url('/'))
                ->withInput($request->except('_token'))
                ->with('error', 'Your session expired for security reasons. Please review your details and submit again.');
        });
    })->create();
