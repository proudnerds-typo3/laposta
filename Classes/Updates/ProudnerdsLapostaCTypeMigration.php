<?php

declare(strict_types=1);

namespace Proudnerds\Laposta\Updates;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Upgrades\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('proudnerdsLapostaCTypeMigration')]
final class ProudnerdsLapostaCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Migrate "Proudnerds Laposta" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The "Proudnerds Laposta" plugins are now registered as content element. Update migrates existing records and backend user permissions.';
    }

    /**
     * This must return an array containing the "list_type" to "CType" mapping
     *
     *  Example:
     *
     *  [
     *      'pi_plugin1' => 'pi_plugin1',
     *      'pi_plugin2' => 'new_content_element',
     *  ]
     *
     * @return array<string, string>
     */
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'laposta_subscribe' => 'laposta_subscribe',
            'laposta_unsubscribe' => 'laposta_unsubscribe',
        ];
    }
}
