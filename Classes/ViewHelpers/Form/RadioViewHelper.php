<?php
namespace SpoonerWeb\FluidFormElements\ViewHelpers\Form;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RadioViewHelper
 *
 * @author Thomas Löffler <loeffler@spooner-web.de>
 */
class RadioViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\RadioViewHelper
{
    use \SpoonerWeb\FluidFormElements\ViewHelpers\FormFieldTrait;

    /**
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->reflectionService = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Reflection\ReflectionService::class);
        $this->className = $this->getClassName();
        $this->propertyPath = $this->arguments['property'];
        $this->templateName = 'CheckRadio';
        $this->additionalClass = 'form-check-input';
    }
}
