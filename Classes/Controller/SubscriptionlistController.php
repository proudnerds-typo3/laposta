<?php

declare(strict_types=1);

namespace Proudnerds\Laposta\Controller;

use Proudnerds\Laposta\Domain\Model\Subscriptionlist;
use Proudnerds\Laposta\Domain\Repository\SubscriptionlistRepository;
use Proudnerds\Laposta\ViewHelpers\MessageBlocksViewHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/***
 *
 * This file is part of the "Laposta" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Jacco van der Post <support@proudnerds.com>, Proud Nerds
 *
 ***/

/**
 * SubscribtionlistController
 */
class SubscriptionlistController extends ActionController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected SubscriptionlistRepository $subscriptionlistRepository;

    protected RequestFactory $requestFactory;

    public function __construct(
        SubscriptionlistRepository $subscriptionlistRepository,
        RequestFactory $requestFactory
    ) {
        $this->subscriptionlistRepository = $subscriptionlistRepository;
        $this->requestFactory = $requestFactory;
    }

    /**
     * action subscribe
     *
     * Messages are passed through the flash message queue (see restAction) and rendered
     * in the template with <f:flashMessages>; they no longer travel in the URL.
     */
    public function subscribeAction(): ResponseInterface
    {
        $lists = null;

        if ($this->settings['lists']) {
            $listUids = explode(',', $this->settings['lists']);

            foreach ($listUids as $listUid) {
                $lists[] = $this->subscriptionlistRepository->findByUid((int)($listUid));
            }
        }

        $this->view->assign('lists', $lists);

        return $this->htmlResponse();
    }

    /**
     * action unsubscribe
     *
     * Messages are passed through the flash message queue (see restAction) and rendered
     * in the template with <f:flashMessages>; they no longer travel in the URL.
     */
    public function unsubscribeAction(): ResponseInterface
    {
        $lists = null;

        if ($this->settings['lists']) {
            $listUids = explode(',', $this->settings['lists']);

            foreach ($listUids as $listUid) {
                $lists[] = $this->subscriptionlistRepository->findByUid((int)($listUid));
            }
        }

        $this->view->assign('lists', $lists);

        return $this->htmlResponse();
    }

    /**
     * action rest
     *
     * Communicate with Laposta API
     *
     * @return ResponseInterface
     */
    public function restAction(): ResponseInterface
    {
        $arguments = $this->request->getArguments();

        if (!isset($arguments['crudAction'])) {
            return $this->redirect('subscribe', 'Subscriptionlist', 'laposta');
        }

        $crudAction = htmlspecialchars($arguments['crudAction']);
        $ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');

        /** @var NormalizedParams $normalizedParams */
        $normalizedParams = $this->request->getAttribute('normalizedParams');
        $sourceUrl = $normalizedParams->getRequestUrl();

        // Honey trap field not field in?
        if (!isset($arguments['laposta.important']) || !$arguments['laposta.important']) {
            $customFieldLabel = htmlspecialchars($this->settings['customFieldNameStartsWith']);
            $ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');

            // Get all the custom fields, these fields the customer can create in Laposta administration
            // You can add any fields in the template, just put customFieldLabel before each fieldname
            $argumentKeys = array_keys($arguments);
            $customFieldKeys = [];
            $customFields = [];

            foreach ($argumentKeys as $argumentKey) {
                if (str_contains($argumentKey, $customFieldLabel)) {
                    $customFieldKeys[] = str_replace($customFieldLabel, '', $argumentKey);
                }
            }

            foreach ($customFieldKeys as $customFieldKey) {
                $customFields[$customFieldKey] = htmlspecialchars($arguments[$customFieldLabel . $customFieldKey]);
            }

            $listUids = [];

            if (isset($arguments['email'])) {
                $email = htmlspecialchars($arguments['email']);
                $numberOfLists = (int)($arguments['numberOfLists']);

                for ($subscriptionNumber = 1; $subscriptionNumber <= $numberOfLists; $subscriptionNumber++) {
                    $listItem = 'list_' . $subscriptionNumber;

                    if ($arguments[$listItem]) {
                        $listUids[] = htmlspecialchars($arguments[$listItem]);
                    }
                }

                if (count($listUids) > 0) {
                    // Collect the outcome per newsletter so we can show one grouped message per
                    // outcome afterwards (shared text once + the newsletters as a list), instead
                    // of one repetitive alert block per newsletter.
                    $subscribed = [];
                    $doubleOptin = [];
                    $alreadyRegistered = [];
                    $unsubscribed = [];
                    $unknownMember = [];
                    $networkError = false;

                    foreach ($listUids as $listUid) {
                        $list = null;
                        try {
                            /** @var Subscriptionlist $list */
                            $list = $this->subscriptionlistRepository->findByUid((int)$listUid);

                            $tryUrl = htmlspecialchars($this->settings['apiUrl']);

                            // Add a subscription
                            if ($crudAction === 'create') {
                                $additionalOptions = [
                                    'auth' => [htmlspecialchars($this->settings['apiKey']), ''],
                                    'form_params' => [
                                        'list_id' => htmlspecialchars($list->getListId()),
                                        'ip' => $ip,
                                        'email' => $email,
                                        'source_url' => $sourceUrl,
                                        'custom_fields' => $customFields,
                                    ],
                                ];

                                $response = $this->requestFactory->request($tryUrl, 'POST', $additionalOptions);

                                // Success on subscribing (code 201)
                                if ($response->getStatusCode() === 201) {
                                    if ($list->getDoubleOptIn()) {
                                        $doubleOptin[] = $list->getListLabel();
                                    } else {
                                        $subscribed[] = $list->getListLabel();
                                    }
                                    $this->writeLog(LogLevel::INFO, $email . ' -> subscribed -> ' . $list->getListLabel());
                                }
                            }

                            // Delete a subscription
                            if ($crudAction === 'delete') {
                                $additionalOptions = [
                                    'auth' => [htmlspecialchars($this->settings['apiKey']), ''],
                                ];

                                $tryUrl = $tryUrl . '/' . $email . '?list_id=' . htmlspecialchars($list->getListId());

                                $response = $this->requestFactory->request($tryUrl, 'DELETE', $additionalOptions);

                                // OK, user deleted from list (code 200)
                                if ($response->getStatusCode() === 200) {
                                    $unsubscribed[] = $list->getListLabel();
                                    $this->writeLog(LogLevel::INFO, $email . ' -> unsubscribed -> ' . $list->getListLabel());
                                }
                            }
                        } catch (\Exception $e) {
                            $response = $e->getMessage();

                            if ($crudAction === 'create') {
                                // E-mail already registered (code 204)
                                if (str_contains($response, '"code": 204')) {
                                    $alreadyRegistered[] = $list?->getListLabel() ?? '';
                                    $this->writeLog(LogLevel::INFO, $email . ' -> already registered -> ' . ($list?->getListLabel() ?? ''));
                                } else {
                                    $networkError = true;
                                    $this->writeLog(
                                        LogLevel::WARNING,
                                        'Action: subscribe / create. ' . $response . ' User ip: ' . $ip . ', user email: ' . $email . ', source url: ' . $sourceUrl
                                    );
                                }
                            }

                            if ($crudAction === 'delete') {
                                // Member does not exist (code 202)
                                if (str_contains($response, '"code": 202')) {
                                    $unknownMember[] = $list?->getListLabel() ?? '';
                                    $this->writeLog(LogLevel::INFO, $email . ' -> unknown member -> ' . ($list?->getListLabel() ?? ''));
                                } else {
                                    $networkError = true;
                                    $this->writeLog(
                                        LogLevel::WARNING,
                                        'Action: unsubscribe / delete. ' . $response . ' User ip: ' . $ip . ', user email: ' . $email . ', source url: ' . $sourceUrl
                                    );
                                }
                            }
                        }
                    }

                    // One grouped message per outcome: intro line + the newsletters as a list.
                    if ($subscribed !== []) {
                        $this->addListFlashMessage(
                            LocalizationUtility::translate('tx_laposta.message.subscribedIntro', 'laposta') ?? '',
                            $subscribed,
                            '',
                            ContextualFeedbackSeverity::OK
                        );
                    }
                    if ($doubleOptin !== []) {
                        $this->addListFlashMessage(
                            LocalizationUtility::translate('tx_laposta.message.doubleOptinIntro', 'laposta') ?? '',
                            $doubleOptin,
                            LocalizationUtility::translate('tx_laposta.message.doubleOptinOutro', 'laposta') ?? '',
                            ContextualFeedbackSeverity::INFO
                        );
                    }
                    if ($alreadyRegistered !== []) {
                        $this->addListFlashMessage(
                            LocalizationUtility::translate('tx_laposta.warning.alreadyRegisteredIntro', 'laposta') ?? '',
                            $alreadyRegistered,
                            '',
                            ContextualFeedbackSeverity::WARNING
                        );
                    }
                    if ($unsubscribed !== []) {
                        $this->addListFlashMessage(
                            LocalizationUtility::translate('tx_laposta.message.unsubscribedIntro', 'laposta') ?? '',
                            $unsubscribed,
                            '',
                            ContextualFeedbackSeverity::OK
                        );
                    }
                    if ($unknownMember !== []) {
                        $this->addListFlashMessage(
                            LocalizationUtility::translate('tx_laposta.warning.unknownMemberIntro', 'laposta', [$email]) ?? '',
                            $unknownMember,
                            '',
                            ContextualFeedbackSeverity::WARNING
                        );
                    }
                    if ($networkError) {
                        $this->addParagraphedFlashMessage(
                            [LocalizationUtility::translate('tx_laposta.warning.network', 'laposta') ?? ''],
                            ContextualFeedbackSeverity::ERROR
                        );
                    }
                } else {
                    // No newslist checked by user
                    $this->addParagraphedFlashMessage(
                        [LocalizationUtility::translate('tx_laposta.warning.lists', 'laposta') ?? ''],
                        ContextualFeedbackSeverity::WARNING
                    );
                }
            } else {
                // No email given by user
                $this->addParagraphedFlashMessage(
                    [LocalizationUtility::translate('tx_laposta.warning.email', 'laposta')],
                    ContextualFeedbackSeverity::WARNING
                );
            }
        } else {
            if (((int)($this->settings['logHoneyTrap'])) === 1) {
                $this->writeLog(
                    LogLevel::INFO,
                    'Spam attempt? Honey pot field filled with: ' . htmlspecialchars($arguments['laposta.important']) . '. User ip: ' . $ip . ', source url: ' . $sourceUrl
                );
            }
        }

        if ($crudAction === 'delete') {
            return $this->redirect('unsubscribe', 'Subscriptionlist', 'laposta');
        }

        return $this->redirect('subscribe', 'Subscriptionlist', 'laposta');
    }

    /**
     * Add one flash message: an intro paragraph, the given newsletter names as a bulleted
     * list, and an optional outro paragraph. A single name is rendered as a plain paragraph
     * (the partial turns a one-item list into a paragraph). Empty names are skipped.
     *
     * @param array<int, string> $labels
     */
    private function addListFlashMessage(string $intro, array $labels, string $outro, ContextualFeedbackSeverity $severity): void
    {
        $paragraphs = [$intro];
        foreach ($labels as $label) {
            $label = trim($label);
            if ($label !== '') {
                $paragraphs[] = MessageBlocksViewHelper::LIST_ITEM_PREFIX . $label;
            }
        }
        if ($outro !== '') {
            $paragraphs[] = $outro;
        }

        $this->addParagraphedFlashMessage($paragraphs, $severity);
    }

    /**
     * Add a single flash message whose body consists of separate paragraphs.
     *
     * Paragraphs are separated by a newline; the Fluid partial renders them as separate
     * <p> elements (no <br>, no HTML built in PHP). Whitespace inside each paragraph is
     * collapsed so formatting in the .xlf files cannot affect the paragraph splitting.
     *
     * @param array<int, string> $paragraphs
     */
    private function addParagraphedFlashMessage(array $paragraphs, ContextualFeedbackSeverity $severity): void
    {
        $paragraphs = array_values(array_filter(
            array_map(
                static fn(string $paragraph): string => trim((string)preg_replace('/\s+/', ' ', $paragraph)),
                $paragraphs
            ),
            static fn(string $paragraph): bool => $paragraph !== ''
        ));

        if ($paragraphs === []) {
            return;
        }

        $this->addFlashMessage(implode("\n", $paragraphs), '', $severity);
    }

    /**
     * Write a log entry, but only when logging is enabled in the plugin settings.
     */
    private function writeLog(string $level, string $message): void
    {
        if (($this->settings['enableLog'] ?? '') === '1') {
            $this->logger->log($level, $message);
        }
    }
}
