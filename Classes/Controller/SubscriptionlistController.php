<?php

namespace Proudnerds\Laposta\Controller;

use Proudnerds\Laposta\Domain\Model\Subscriptionlist;
use Proudnerds\Laposta\Domain\Repository\SubscriptionlistRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Http\RequestFactory;
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

    /**
     *
     * @var SubscriptionlistRepository
     */
    protected SubscriptionlistRepository $subscriptionlistRepository;

    public function __construct(
        SubscriptionlistRepository $subscriptionlistRepository,
        RequestFactory             $requestFactory
    )
    {
        $this->subscriptionlistRepository = $subscriptionlistRepository;
        $this->requestFactory = $requestFactory;
    }

    /** @var RequestFactory */
    protected $requestFactory = null;

    /**
     * action subscribe
     *
     * @param array|null $messages
     * @return ResponseInterface
     */
    public function subscribeAction(array $messages = null): ResponseInterface
    {
        $lists = null;

        if ($this->settings['lists']) {
            $listUids = explode(',', $this->settings['lists']);

            foreach ($listUids as $listUid) {
                $lists[] = $this->subscriptionlistRepository->findByUid((int)($listUid));
            }
        }

        $this->view->assignMultiple([
            'lists' => $lists,
            'messages' => $messages
        ]);

        return $this->htmlResponse();
    }

    /**
     * action unsubscribe
     *
     * @param array|null $messages
     * @return ResponseInterface
     */
    public function unsubscribeAction(array $messages = null): ResponseInterface
    {
        $lists = null;

        if ($this->settings['lists']) {
            $listUids = explode(',', $this->settings['lists']);

            foreach ($listUids as $listUid) {
                $lists[] = $this->subscriptionlistRepository->findByUid((int)($listUid));
            }
        }

        $this->view->assignMultiple([
            'lists' => $lists,
            'messages' => $messages
        ]);

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
        $messages = [];
        $arguments = $this->request->getArguments();

        if (!isset($arguments['crudAction'])) {
            return $this->redirect('subscribe', 'Subscriptionlist', 'laposta');
        }

        $crudAction = htmlspecialchars($arguments['crudAction']);
        $ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');

        $request = $GLOBALS['TYPO3_REQUEST'];

        /** @var NormalizedParams $normalizedParams */
        $normalizedParams = $request->getAttribute('normalizedParams');
        $sourceUrl = $normalizedParams->getRequestUrl();

        $enableLog = false;
        if ($this->settings['enableLog'] === '1') {
            $enableLog = true;
        }

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
                    foreach ($listUids as $listUid) {
                        try {
                            /** @var Subscriptionlist $list */
                            $list = $this->subscriptionlistRepository->findByUid($listUid);

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
                                        'custom_fields' => $customFields
                                    ]
                                ];

                                $response = $this->requestFactory->request($tryUrl, 'POST', $additionalOptions);

                                // Success on subscribing (code 201)
                                if ($response->getStatusCode() === 201) {
                                    if ($list->getDoubleOptIn()) {
                                        // Double opt-in, user needs to verify his registration with email
                                        $registrationMessage = LocalizationUtility::translate(
                                                'tx_laposta.message.doubleOptinSubscribed',
                                                'laposta'
                                            ) . ' ' . $list->getListLabel();
                                        $verificationMessage = LocalizationUtility::translate(
                                            'tx_laposta.message.emailVerification',
                                            'laposta'
                                        );
                                        $messages[] = $registrationMessage . '.<br/>' . $verificationMessage;
                                        if ($enableLog) {
                                            $this->logger->log(
                                                LogLevel::INFO,
                                                $email . ' -> ' . $registrationMessage . ' ' . $verificationMessage
                                            );
                                        }
                                    } else {
                                        // No double opt-in, user is subscribed
                                        $subscribeMessage = LocalizationUtility::translate(
                                                'tx_laposta.message.subscribed',
                                                'laposta'
                                            ) . ' ' . $list->getListLabel();
                                        $messages[] = $subscribeMessage;
                                        if ($enableLog) {
                                            $this->logger->log(LogLevel::INFO, $email . ' -> ' . $subscribeMessage);
                                        }
                                    }
                                }
                            }

                            // Delete a subscription
                            if ($crudAction === 'delete') {
                                $additionalOptions = [
                                    'auth' => [htmlspecialchars($this->settings['apiKey']), '']
                                ];

                                $tryUrl = $tryUrl . '/' . $email . '?list_id=' . htmlspecialchars($list->getListId());

                                $response = $this->requestFactory->request($tryUrl, 'DELETE', $additionalOptions);

                                // OK, user deleted from list (code 200)
                                if ($response->getStatusCode() === 200) {
                                    $deletedMessage = LocalizationUtility::translate(
                                            'tx_laposta.message.unsubscribed',
                                            'laposta'
                                        ) . ' ' . $list->getListLabel();
                                    $messages[] = $deletedMessage;
                                    if ($enableLog) {
                                        $this->logger->log(LogLevel::INFO, $email . ' -> ' . $deletedMessage);
                                    }
                                }
                            }
                        } catch (
                        \Exception $e
                        ) {
                            $response = $e->getMessage();

                            if ($crudAction === 'create') {
                                // E-mail already registered (code 204)
                                if (str_contains($response, '"code": 204')) {
                                    $emailAlreadyRegisteredMessage = LocalizationUtility::translate(
                                            'tx_laposta.warning.emailregistered',
                                            'laposta'
                                        ) . ' ' . htmlspecialchars($list->getListLabel());
                                    $messages[] = $emailAlreadyRegisteredMessage;
                                    if ($enableLog) {
                                        $this->logger->log(
                                            LogLevel::INFO,
                                            $email . ' -> ' . $emailAlreadyRegisteredMessage
                                        );
                                    }
                                } else {
                                    // Some error while trying to POST to Laposta
                                    $netWorkError = LocalizationUtility::translate(
                                        'tx_laposta.warning.network',
                                        'laposta'
                                    );
                                    $messages[] = $netWorkError;
                                    if ($enableLog) {
                                        $this->logger->log(
                                            LogLevel::WARNING,
                                            'Action: subscribe / create. ' . $netWorkError . '. ' . $response . ' User ip: ' . $ip . ', user email: ' . $email . ', source url: ' . $sourceUrl
                                        );
                                    }
                                }
                            }

                            if ($crudAction === 'delete') {
                                // Member does not exist
                                if (str_contains($response, '"code": 202')) {
                                    $unknownMemberMessage = LocalizationUtility::translate(
                                            'tx_laposta.warning.invalidMember1',
                                            'laposta'
                                        ) . ' ' . htmlspecialchars($list->getListLabel()) . ' ' . LocalizationUtility::translate(
                                            'tx_laposta.warning.invalidMember2',
                                            'laposta'
                                        ) . ' ' . $email;
                                    $messages[] = $unknownMemberMessage;
                                    if ($enableLog) {
                                        $this->logger->log(LogLevel::INFO, $unknownMemberMessage);
                                    }
                                } else {
                                    // Some error while trying to POST to Laposta
                                    $netWorkError = LocalizationUtility::translate(
                                        'tx_laposta.warning.network',
                                        'laposta'
                                    );
                                    $messages[] = $netWorkError;
                                    if ($enableLog) {
                                        $this->logger->log(
                                            LogLevel::WARNING,
                                            'Action: unsubscribe / delete. ' . $netWorkError . '. ' . $response . 'User ip: ' . $ip . ', user email: ' . $email . ', source url: ' . $sourceUrl
                                        );
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // No newslist checked by user
                    $messages[] = LocalizationUtility::translate('tx_laposta.warning.lists', 'laposta');
                }
            } else {
                // No email given by user
                $messages[] = LocalizationUtility::translate('tx_laposta.warning.email', 'laposta');
            }
        } else {
            if ($enableLog && ((int)($this->settings['logHoneyTrap']) === 1)) {
                $this->logger->log(
                    LogLevel::INFO,
                    'Spam attempt? Honey pot field filled with: ' . htmlspecialchars($arguments['laposta.important']) . '. User ip: ' . $ip . ', source url: ' . $sourceUrl
                );
            }
        }

        if ($crudAction === 'create') {
            return $this->redirect('subscribe', 'Subscriptionlist', 'laposta', ['messages' => $messages]);
        }

        if ($crudAction === 'delete') {
            return $this->redirect('unsubscribe', 'Subscriptionlist', 'laposta', ['messages' => $messages]);
        }
    }
}
