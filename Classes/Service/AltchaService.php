<?php

declare(strict_types=1);

namespace Proudnerds\Laposta\Service;

use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\Payload;
use AltchaOrg\Altcha\VerifySolutionOptions;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Crypto\HashAlgo;
use TYPO3\CMS\Core\Crypto\HashService;

/**
 * Creates and verifies ALTCHA proof-of-work challenges (https://altcha.org) for the
 * subscribe and unsubscribe forms. Runs fully on the own server; no external service.
 */
final class AltchaService
{
    private const DEFAULT_COST = 10000;
    private const DEFAULT_LIFETIME = 1800;

    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly HashService $hashService,
        private readonly ExtensionConfiguration $extensionConfiguration,
    ) {}

    /**
     * altcha-org/altcha is a Composer dependency; installations without it cannot use ALTCHA.
     */
    public static function isAvailable(): bool
    {
        return class_exists(Altcha::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function createChallenge(): array
    {
        return $this->createAltcha()->createChallenge(new CreateChallengeOptions(
            algorithm: new Pbkdf2(),
            cost: $this->getCost(),
            expiresAt: time() + $this->getLifetime(),
        ))->toArray();
    }

    /**
     * Each solved challenge is accepted once, otherwise a bot could keep reusing one solution.
     */
    public function verify(mixed $base64Payload): bool
    {
        if (!self::isAvailable() || !is_string($base64Payload) || $base64Payload === '') {
            return false;
        }

        try {
            $payload = Payload::fromBase64($base64Payload);
        } catch (\InvalidArgumentException) {
            return false;
        }

        $result = $this->createAltcha()->verifySolution(new VerifySolutionOptions(
            algorithm: new Pbkdf2(),
            payload: $payload,
        ));
        if (!$result->verified) {
            return false;
        }

        $usedChallenges = $this->cacheManager->getCache('laposta_altcha');
        $cacheIdentifier = sha1((string)$payload->challenge->signature);
        if ($usedChallenges->has($cacheIdentifier)) {
            return false;
        }
        $usedChallenges->set($cacheIdentifier, '1', [], $this->getLifetime());

        return true;
    }

    private function createAltcha(): Altcha
    {
        // Derived from the encryption key, so all web servers of an installation share it without extra configuration
        return new Altcha(hmacSignatureSecret: $this->hashService->hmac('altcha-challenge', 'laposta-altcha', HashAlgo::SHA256));
    }

    private function getCost(): int
    {
        return max(1, (int)($this->getConfiguration()['altchaCost'] ?? self::DEFAULT_COST));
    }

    private function getLifetime(): int
    {
        return max(60, (int)($this->getConfiguration()['altchaLifetime'] ?? self::DEFAULT_LIFETIME));
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
