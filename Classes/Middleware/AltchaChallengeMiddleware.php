<?php

declare(strict_types=1);

namespace Proudnerds\Laposta\Middleware;

use Proudnerds\Laposta\Service\AltchaService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * Serves a fresh ALTCHA challenge to the widget in the subscribe and unsubscribe forms.
 */
final class AltchaChallengeMiddleware implements MiddlewareInterface
{
    public const PATH = '/laposta/altcha-challenge';

    public function __construct(
        private readonly AltchaService $altchaService,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() !== 'GET' || $request->getUri()->getPath() !== self::PATH || !AltchaService::isAvailable()) {
            return $handler->handle($request);
        }

        return new JsonResponse($this->altchaService->createChallenge(), 200, [
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
