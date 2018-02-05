<?php
namespace SpoonerWeb\FluidFormElements\ViewHelpers;

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

use SpoonerWeb\FluidFormElements\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ClassReflection;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Trait FormFieldTrait
 *
 * @author Thomas Löffler <loeffler@spooner-web.de>
 */
trait FormFieldTrait
{

    /**
     * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @var string
     */
    protected $className = '';

    /**
     * @var string
     */
    protected $propertyPath = '';

    /**
     * @var string
     */
    protected $templateName = 'Default';

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('label', 'string', 'If specified, will use this label instead of the determined one.');
    }

    /**
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->reflectionService = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Reflection\ReflectionService::class);
        $this->className = $this->getClassName();
        $this->propertyPath = $this->arguments['property'];
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        if (!$this->viewHelperVariableContainer->exists(\TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper::class, 'formObject')) {
            return '';
        }

        $formObject = $this->viewHelperVariableContainer->get(\TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper::class, 'formObject');

        return get_class($formObject);
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return mixed
     */
    public function render()
    {
        // override id with propertyPath if none given
        if (empty($this->arguments['id'])) {
            $this->tag->addAttribute('id', $this->propertyPath);
        }
        // Add bootstrap class
        $class = $this->arguments['class'] ? $this->arguments['class'] . ' form-control' : 'form-control';
        $this->tag->addAttribute('class', $class);

        $content = parent::render();
        $template = $this->getTemplate();
        $template->assign('content', $content);

        return $template->render();
    }

    /**
     * @return array
     */
    public function getFieldClasses(): array
    {
        $fieldClasses = [];
        if ($this->viewHelperVariableContainer->exists(\TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper::class, 'fieldClasses')) {
            $fieldClasses = $this->viewHelperVariableContainer->get(
                \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper::class,
                'fieldClasses'
            );
        }

        return $fieldClasses;
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    public function getTemplate(): \TYPO3\CMS\Fluid\View\StandaloneView
    {
        $standalone = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $templateFile = 'EXT:fluid_form_elements/Resources/Private/Templates/' . ConfigurationService::getSelectedFormFieldLayoutAsFolder() . '/' . $this->templateName . '.html';
        $pathToTemplateFile = GeneralUtility::getFileAbsFileName($templateFile);
        $standalone->setTemplatePathAndFilename($pathToTemplateFile);

        // If there is a file in the other extension in Resources/Private/Templates/FluidFormElements/Default.html, use this
        $overrideTemplateFileByUsedExtension = 'EXT:' . GeneralUtility::camelCaseToLowerCaseUnderscored($this->extractExtensionNameFromClassName($this->className)) . '/Resources/Private/Templates/FluidFormElements/' . $this->templateName . '.html';
        $pathToOverridingTemplateFile = GeneralUtility::getFileAbsFileName($overrideTemplateFileByUsedExtension);
        if (file_exists($pathToOverridingTemplateFile)) {
            $standalone->setTemplatePathAndFilename($pathToOverridingTemplateFile);
        }

        $standalone->assignMultiple(
            [
                'label' => $this->getLabel($this->objectManager->getEmptyObject($this->className), $this->propertyPath),
                'required' => $this->getRequirementFromModel(),
                'propertyPath' => $this->propertyPath,
                'classLabelColumn' => $this->getFieldClasses()['classLabelColumn'],
                'classFieldColumn' => $this->getFieldClasses()['classFieldColumn']
            ]
        );

        return $standalone;
    }

    /**
     * Search for translations in locallang_db.xlf with the following translation key schema
     * lower_case_table_name.property_name
     *
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $model
     * @param string $argumentPropertyString
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return string
     */
    public function getLabel(\TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $model, string $argumentPropertyString): string
    {
        if (!empty($this->arguments['label'])) {
            return $this->arguments['label'];
        }
        $extensionNameBasedOnModel = $this->extractExtensionNameFromClassName(get_class($model));
        $tableNameBasedOnModel = $this->getTableName($model);
        $localisationIdentifier = $tableNameBasedOnModel . '.' . GeneralUtility::camelCaseToLowerCaseUnderscored($argumentPropertyString);
        $label = LocalizationUtility::translate($localisationIdentifier, $extensionNameBasedOnModel);

        if ($label === null) {
            // If we got no label yet:
            // Try if there is a sub property in the argument property string
            // and extract the property name before the dot.
            $propertyName = strstr($argumentPropertyString, '.', -1);

            // If there is no sub property and no translation, we can't do anymore...
            if ($propertyName === false) {
                throw new \UnexpectedValueException('No translation found for model "' . $localisationIdentifier . '"!', 1517580724);
            }

            // Try to get the property's class name by reflection
            /** @var \TYPO3\CMS\Extbase\Reflection\ClassReflection $classReflection */
            $classReflection = GeneralUtility::makeInstance(ClassReflection::class, get_class($model));
            $varValues = $classReflection->getProperty($propertyName)->getTagValues('var');
            $subModelClassName = array_shift($varValues);

            // Create a new instance of the property (sub model) and
            // try to get the matching translated label
            if (class_exists($subModelClassName)) {
                $subModel = $this->objectManager->getEmptyObject($subModelClassName);
                // Take the nested (the rest after the dot) argument property string
                $argumentPropertyStringNested = substr($argumentPropertyString, strpos($argumentPropertyString, '.') + 1);

                $label = $this->getLabel($subModel, $argumentPropertyStringNested);
            } else {
                throw new \BadMethodCallException('Class "' . $subModelClassName . '" does not exist, but is defined in "' . get_class($model) . '"!', 1517580732);
            }
        }

        return $label;
    }

    /**
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $model
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return string
     */
    protected function  getTableName(\TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $model): string
    {
        $dataMapper = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class);

        return $dataMapper->getDataMap(get_class($model))->getTableName();
    }

    /**
     * Checks if the given property has a @validate tag with "NotEmpty" in it
     *
     * @return bool
     */
    protected function getRequirementFromModel(): bool
    {
        $required = false;
        if ($this->propertyPath !== '') {
            // Get the property and it's type
            $property = $this->propertyPath;
            /** @var ClassReflection $classReflection */
            $classReflection = GeneralUtility::makeInstance(ClassReflection::class, $this->className);

            // Check for nestest properties too; seperated in Extbase style by '.'.
            if (strpos($property, '.')) {
                $propertyParts = GeneralUtility::trimExplode('.', $property);
                $propertyType = false;
                foreach ($propertyParts as $key => $propertyPart) {
                    if ($key && $propertyType) {
                        $classReflection = GeneralUtility::makeInstance(ClassReflection::class, $propertyType);
                    }
                    $varValues = $classReflection->getProperty($propertyPart)->getTagValues('var');
                    $propertyType = array_shift($varValues);
                    if ($classReflection->getProperty($propertyPart)->isTaggedWith('validate')) {
                        $validateValues = $classReflection->getProperty($propertyPart)->getTagValues('validate');
                        $required = GeneralUtility::inList(array_shift($validateValues), 'NotEmpty');
                    }
                }
            } else {
                if ($classReflection->getProperty($property)->isTaggedWith('validate')) {
                    $validateValues = $classReflection->getProperty($property)->getTagValues('validate');
                    $required = GeneralUtility::inList(array_shift($validateValues), 'NotEmpty');
                }
            }
        }

        return $required;
    }

    /**
     * @return string
     */
    public function getPropertyType(): string
    {
        /** @var ClassReflection $classReflection */
        $classReflection = GeneralUtility::makeInstance(ClassReflection::class, $this->className);

        // Check for nested properties too; separated in Extbase style by '.'.
        if (strpos($this->propertyPath, '.')) {
            $propertyParts = GeneralUtility::trimExplode('.', $this->propertyPath);
            $propertyType = false;
            foreach ($propertyParts as $key => $propertyPart) {
                if ($key && $propertyType) {
                    $classReflection = GeneralUtility::makeInstance(ClassReflection::class, $propertyType);
                }
                $tagValues = $classReflection->getProperty($propertyPart)->getTagValues('var');
                $propertyType = array_shift($tagValues);
            }
        } else {
            $tagValues = $classReflection->getProperty($this->propertyPath)->getTagValues('var');
            $propertyType = array_shift($tagValues);
        }

        return $propertyType;
    }

    /**
     * Get repository className from PropertyType/DomainModelClassName
     *
     * @param string $modelClassName
     * @return string
     */
    protected function getRepositoryClassNameFromModelClassName(string $modelClassName): string
    {
        $className = str_replace(
            '\\Domain\\Model\\',
            '\\Domain\\Repository\\',
            $modelClassName
        ) . 'Repository';

        return $className;
    }

    /**
     * get extensionName out of the domain model className
     *
     * @param string $className
     * @return string
     */
    protected function extractExtensionNameFromClassName(string $className): string
    {
        $nsSeparator = '\\';

        $classNameAsArray = explode($nsSeparator, $className);
        $extensionName = $classNameAsArray[1];

        if ($classNameAsArray[0] === 'TYPO3' && $classNameAsArray[1] === 'CMS') {
            $extensionName = $classNameAsArray[2];
        }

        return $extensionName;
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    private function getLanguageService(): \TYPO3\CMS\Lang\LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
