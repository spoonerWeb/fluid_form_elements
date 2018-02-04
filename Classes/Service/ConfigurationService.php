<?php
namespace SpoonerWeb\FluidFormElements\Service;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class ConfigurationService
 *
 * @author Thomas Löffler <loeffler@spooner-web.de>
 */
class ConfigurationService
{

    /**
     * @var array
     */
    protected static $configuration = [];

    /**
     * @var array
     */
    protected static $layoutMapping = [
        0 => 'BootstrapDefault',
        1 => 'BootstrapHorizontal'
    ];

    /**
     * @return void
     */
    protected static function initConfiguration()
    {
        self::$configuration = @unserialize(
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['fluid_form_elements'],
            ['allowed_classes' => false]
        );
    }

    /**
     * @return int
     */
    public static function getSelectedFormFieldLayout()
    {
        self::initConfiguration();
        if (!isset(self::$configuration['formFieldLayout'])) {
            return 0;
        }

        return self::$configuration['formFieldLayout'];
    }

    /**
     * @return string
     */
    public static function getSelectedFormFieldLayoutAsFolder()
    {
        return self::$layoutMapping[self::getSelectedFormFieldLayout()];
    }
}
