<?php

namespace Proudnerds\Laposta\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\Validate;

/**
 * Subscriptionlist
 */
class Subscriptionlist extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * The label of the list to subscribe to
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $listLabel = '';

    /**
     * The Laposta id of the list to subscribe to
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $listId = '';

    /**
     * Is there double opt-in for this list
     *
     * @var bool
     */
    protected bool $doubleOptIn = false;

    /**
     * Additional information on this list
     *
     * @var string
     */
    protected string $info = '';

    /**
     * Returns the listLabel
     *
     * @return string $listLabel
     */
    public function getListLabel(): string
    {
        return $this->listLabel;
    }

    /**
     * Sets the listLabel
     *
     * @param string $listLabel
     * @return void
     */
    public function setListLabel(string $listLabel): void
    {
        $this->listLabel = $listLabel;
    }

    /**
     * Returns the listId
     *
     * @return string $listId
     */
    public function getListId(): string
    {
        return $this->listId;
    }

    /**
     * Sets the listId
     *
     * @param string $listId
     * @return void
     */
    public function setListId(string $listId): void
    {
        $this->listId = $listId;
    }

    /**
     * Returns the doubleOptIn
     *
     * @return bool
     */
    public function getDoubleOptIn(): bool
    {
        return $this->doubleOptIn;
    }

    /**
     * Sets the doubleOptIn
     *
     * @param bool $doubleOptIn
     * @return void
     */
    public function setDoubleOptIn(bool $doubleOptIn): void
    {
        $this->doubleOptIn = $doubleOptIn;
    }

    /**
     * Returns the info
     *
     * @return string $info
     */
    public function getInfo(): string
    {
        return $this->info;
    }

    /**
     * Sets the info
     *
     * @param string $info
     * @return void
     */
    public function setInfo(string $info): void
    {
        $this->info = $info;
    }
}
