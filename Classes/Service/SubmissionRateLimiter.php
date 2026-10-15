<?php

declare(strict_types=1);

namespace Proudnerds\Laposta\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\RateLimiter\RateLimiterFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Limits the number of form submissions per IP address. Disabled while the extension
 * configuration rateLimit is 0.
 */
final class SubmissionRateLimiter
{
    private const DEFAULT_INTERVAL = '1 hour';

    public function __construct(
        private readonly ExtensionConfiguration $extensionConfiguration,
    ) {}

    /**
     * Counts one submission of the given form and tells whether it is still within the limit.
     */
    public function consume(ServerRequestInterface $request, string $form): bool
    {
        $configuration = $this->getConfiguration();
        $limit = (int)($configuration['rateLimit'] ?? 0);
        if ($limit < 1) {
            return true;
        }

        $interval = trim((string)($configuration['rateLimitInterval'] ?? ''));

        // Created here instead of injected: its storage collects garbage when constructed,
        // which should not happen on every view of the uncached form
        return GeneralUtility::makeInstance(RateLimiterFactory::class)->createRequestBasedLimiter($request, [
            'id' => 'laposta-' . $form,
            'policy' => 'sliding_window',
            'limit' => $limit,
            'interval' => $interval !== '' ? $interval : self::DEFAULT_INTERVAL,
        ])->consume()->isAccepted();
    }

    /**
     * @return array<string, mixed>
     */
    private function getConfiguration(): array
    {
        try {
            $configuration = $this->extensionConfiguration->get('laposta');
        } catch (\Throwable) {
            return [];
        }

        return is_array($configuration) ? $configuration : [];
    }
}
