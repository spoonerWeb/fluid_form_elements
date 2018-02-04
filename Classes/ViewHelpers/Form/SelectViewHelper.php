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

use SpoonerWeb\FluidFormElements\ViewHelpers\FormFieldTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SelectViewHelper
 *
 * @author Thomas Löffler <loeffler@spooner-web.de>
 */
class SelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper
{
    use FormFieldTrait;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @throws \TYPO3\CMS\Extbase\Utility\Exception\InvalidTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return array
     */
    public function getOptions(): array
    {
        $this->configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
        $propertyType = $this->getPropertyType();

        return $this->getObjectsFromRelatedRepository($propertyType);
    }

    /**
     * Get all objects from the related repository
     *
     * @param string $propertyType
     * @throws \TYPO3\CMS\Extbase\Utility\Exception\InvalidTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return array
     */
    protected function getObjectsFromRelatedRepository(string $propertyType): array
    {
        $options = [];
        // check if the propertyType is an object storage, then we need the included class
        $type = \TYPO3\CMS\Extbase\Utility\TypeHandlingUtility::parseType($propertyType);
        if ($type['type'] === \TYPO3\CMS\Extbase\Persistence\ObjectStorage::class) {
            $propertyType = $type['elementType'];
        }

        // check against is_string, because $propertyType can also be bool
        if (is_string($propertyType) && class_exists($propertyType)) {
            $this->overrideConfiguration($propertyType);
            $repositoryClass = $this->getRepositoryClassNameFromModelClassName($propertyType);
            $repository = $this->objectManager->get($repositoryClass);
            $optionsArguments = $repository->findAll()->toArray();
            $this->arguments['optionValueField'] = $this->arguments['optionValueField'] ?: 'uid';
            $this->arguments['optionLabelField'] = $this->getOptionLabelField($propertyType);
            $this->restoreConfiguration();

            foreach ($optionsArguments as $key => $record) {
                $options[$record->{'get' . ucfirst($this->arguments['optionValueField'])}()] = $record;
            }
        }

        return $options;
    }

    /**
     * Returns the name of the field defined in the TCA to use it as label for
     * the option tags.
     *
     * @param string $modelName
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return string
     */
    protected function getOptionLabelField(string $modelName): string
    {
        $tableName = $this->getTableName(GeneralUtility::makeInstance($modelName));

        // Get the label from the TCA
        $optionLabel = $GLOBALS['TCA'][$tableName]['ctrl']['label'];

        // Checks if there is a mapping to this field
        if (isset($this->configuration['persistence']['classes'][$modelName]['mapping']['columns'][$optionLabel]['mapOnProperty'])) {
            $optionLabel = $this->configuration['persistence']['classes'][$modelName]['mapping']['columns'][$optionLabel]['mapOnProperty'];
        }

        return $optionLabel;
    }

    /**
     * Override configuration to request objects from a storagePid
     * which was configured by another extension
     * The extensionName will be extracted from the DomainModelName $propertyType
     *
     * @param string $propertyType
     * @return void
     */
    protected function overrideConfiguration(string $propertyType)
    {
        $configuration = $this->configuration;
        $configuration['extensionName'] = $this->extractExtensionNameFromClassName($propertyType);
        $this->configurationManager->setConfiguration($configuration);
    }

    /**
     * As the ConfigurationManager and its configurations are of type SingleTon,
     * we have to restore the old configuration to be valid for further repository requests
     *
     * @return void
     */
    protected function restoreConfiguration()
    {
        $this->configurationManager->setConfiguration($this->configuration);
    }
}
