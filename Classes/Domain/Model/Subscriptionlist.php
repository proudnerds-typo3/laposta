<?php
namespace Proudnerds\Laposta\Domain\Model;

/**
 * Subscriptionlist
 */
class Subscriptionlist extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * The label of the list to subscribe to
     * 
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $listLabel = '';

    /**
     * The Laposta id of the list to subscribe to
     * 
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $listId = '';

    /**
     * Is there double opt-in for this list
     *
     * @var bool
     */
    protected $doubleOptIn = '';

    /**
     * Additional information on this list
     * 
     * @var string
     */
    protected $info = '';

    /**
     * Returns the listLabel
     * 
     * @return string $listLabel
     */
    public function getListLabel()
    {
        return $this->listLabel;
    }

    /**
     * Sets the listLabel
     * 
     * @param string $listLabel
     * @return void
     */
    public function setListLabel($listLabel)
    {
        $this->listLabel = $listLabel;
    }

    /**
     * Returns the listId
     * 
     * @return string $listId
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * Sets the listId
     * 
     * @param string $listId
     * @return void
     */
    public function setListId($listId)
    {
        $this->listId = $listId;
    }

    /**
     * Returns the doubleOptIn
     *
     * @return bool
     */
    public function getDoubleOptIn()
    {
        return $this->doubleOptIn;
    }

    /**
     * Sets the doubleOptIn
     *
     * @param bool $doubleOptIn
     * @return void
     */
    public function setDoubleOptIn($doubleOptIn)
    {
        $this->doubleOptIn = $doubleOptIn;
    }

    /**
     * Returns the info
     * 
     * @return string $info
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Sets the info
     * 
     * @param string $info
     * @return void
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }
}
