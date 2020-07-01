<?php

namespace Proudnerds\Laposta\Controller;

use Proudnerds\Laposta\Domain\Model\Subscriptionlist;
use Proudnerds\Laposta\Domain\Repository\SubscriptionlistRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
class SubscriptionlistController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * subscriptionlistRepository
     *
     * @var SubscriptionlistRepository
     */
    protected $subscriptionlistRepository = null;

    /**
     * @param SubscriptionlistRepository $subscriptionlistRepository
     */
    public function injectSubscriptionlistRepository(SubscriptionlistRepository $subscriptionlistRepository)
    {
        $this->subscriptionlistRepository = $subscriptionlistRepository;
    }

    /** @var RequestFactory */
    protected $requestFactory = null;

    /**
     * @param RequestFactory $requestFactory
     */
    public function injectRequestFactory(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * action subscribe
     *
     * @param array|null $messages
     * @return void
     */
    public function subscribeAction(array $messages = null)
    {
        $lists = null;

        if ($this->settings['lists']) {
            $listUids = explode(',', $this->settings['lists']);

            foreach ($listUids as $listUid) {
                $lists[] = $this->subscriptionlistRepository->findByUid(intval($listUid));
            }
        }

        $this->view->assignMultiple([
            'lists' => $lists,
            'messages' => $messages
        ]);
    }

    /**
     * action unsubscribe
     *
     * @param array|null $messages
     * @return void
     */
    public function unsubscribeAction(array $messages = null)
    {
        $lists = null;

        if ($this->settings['lists']) {
            $listUids = explode(',', $this->settings['lists']);

            foreach ($listUids as $listUid) {
                $lists[] = $this->subscriptionlistRepository->findByUid(intval($listUid));
            }
        }

        $this->view->assignMultiple([
            'lists' => $lists,
            'messages' => $messages
        ]);
    }

    /**
     * action rest
     *
     * Communicate with Laposta API
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function restAction()
    {
        $messages = [];
        $arguments = $this->request->getArguments();
        $crudAction = htmlspecialchars($arguments['crudAction']);
        $customFieldLabel = htmlspecialchars($this->settings['customFieldNameStartsWith']);
        $ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        $sourceUrl = $this->request->getRequestUri();

        // Get all the custom fields, these fields the customer can create in Laposta administration
        // You can add any fields in the template, just put customField. before each fieldname
        $argumentKeys = array_keys($arguments);
        $customFieldKeys = [];
        $customFields = [];

        foreach ($argumentKeys as $argumentKey) {
            if (strpos($argumentKey, $customFieldLabel) !== false) {
                $customFieldKeys[] = str_replace($customFieldLabel, '', $argumentKey);
            }
        }

        foreach ($customFieldKeys as $customFieldKey) {
            $customFields[$customFieldKey] = htmlspecialchars($arguments[$customFieldLabel . $customFieldKey]);
        }

        $listUids = [];
        $email = htmlspecialchars($arguments['email']);

        if ($email) {
            $numberOfLists = intval($arguments['numberOfLists']);

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
                                    $registrationMessage = LocalizationUtility::translate('tx_laposta.message.doubleOptinSubscribed',
                                            'laposta') . ' ' . $list->getListLabel();
                                    $verificationMessage = LocalizationUtility::translate('tx_laposta.message.emailVerification',
                                        'laposta');
                                    $messages[] = $registrationMessage . '.<br/>' . $verificationMessage;
                                    $this->logger->log(LogLevel::INFO,
                                        $email . ' -> ' . $registrationMessage . ' ' . $verificationMessage);
                                } else {
                                    // No double opt-in, user is subscribed
                                    $subscribeMessage = LocalizationUtility::translate('tx_laposta.message.subscribed',
                                            'laposta') . ' ' . $list->getListLabel();
                                    $messages[] = $subscribeMessage;
                                    $this->logger->log(LogLevel::INFO, $email . ' -> ' . $subscribeMessage);
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
                                $deletedMessage = LocalizationUtility::translate('tx_laposta.message.unsubscribed',
                                        'laposta') . ' ' . $list->getListLabel();
                                $messages[] = $deletedMessage;
                                $this->logger->log(LogLevel::INFO, $email . ' -> ' . $deletedMessage);
                            }
                        }
                    } catch (
                    \Exception $e
                    ) {
                        $response = $e->getMessage();

                        if ($crudAction === 'create') {
                            // E-mail already registered (code 204)
                            if (strpos($response, '"code": 204') !== false) {
                                $emailAlreadyRegisteredMessage = LocalizationUtility::translate('tx_laposta.warning.emailregistered',
                                        'laposta') . ' ' . htmlspecialchars($list->getListLabel());
                                $messages[] = $emailAlreadyRegisteredMessage;
                                $this->logger->log(LogLevel::INFO, $email . ' -> ' . $emailAlreadyRegisteredMessage);
                            } else {
                                // Some error while trying to POST to Laposta
                                $netWorkError = LocalizationUtility::translate('tx_laposta.warning.network', 'laposta');
                                $messages[] = $netWorkError;
                                $this->logger->log(LogLevel::WARNING,
                                    'Action: subscribe / create. ' . $netWorkError . '. ' . $response . ' User ip: ' . $ip . ', user email: ' . $email . ', source url: ' . $sourceUrl);
                            }
                        }

                        if ($crudAction === 'delete') {
                            // Member does not exist
                            if (strpos($response, '"code": 202') !== false) {
                                $unknownMemberMessage = LocalizationUtility::translate('tx_laposta.warning.invalidMember1',
                                        'laposta') . ' ' . htmlspecialchars($list->getListLabel()) . ' ' . LocalizationUtility::translate('tx_laposta.warning.invalidMember2',
                                        'laposta') . ' ' . $email;
                                $messages[] = $unknownMemberMessage;
                                $this->logger->log(LogLevel::INFO, $unknownMemberMessage);
                            } else {
                                // Some error while trying to POST to Laposta
                                $netWorkError = LocalizationUtility::translate('tx_laposta.warning.network', 'laposta');
                                $messages[] = $netWorkError;
                                $this->logger->log(LogLevel::WARNING,
                                    'Action: unsubscribe / delete. ' . $netWorkError . '. ' . $response . 'User ip: ' . $ip . ', user email: ' . $email . ', source url: ' . $sourceUrl);
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

        if ($crudAction === 'create') {
            $this->redirect('subscribe', 'Subscriptionlist', 'laposta', ['messages' => $messages]);
        }

        if ($crudAction === 'delete') {
            $this->redirect('unsubscribe', 'Subscriptionlist', 'laposta', ['messages' => $messages]);
        }
    }
}
