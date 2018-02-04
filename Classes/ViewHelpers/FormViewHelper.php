<?php
namespace SpoonerWeb\FluidFormElements\ViewHelpers;

use SpoonerWeb\FluidFormElements\Service\ConfigurationService;

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

class FormViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('classLabelColumn', 'string', 'Additional class used for label.', false, 'col-sm-3');
        $this->registerArgument('classFieldColumn', 'string', 'Additional class used for field.', false, 'col-sm-9');
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $this->addColumnClassesToViewHelperContainer();

        return parent::render();
    }

    /**
     * @return void
     */
    protected function addColumnClassesToViewHelperContainer()
    {
        $this->viewHelperVariableContainer->add(
            \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper::class,
            'fieldClasses',
            [
                'classLabelColumn' => $this->arguments['classLabelColumn'],
                'classFieldColumn' => $this->arguments['classFieldColumn']
            ]
        );
    }
}
