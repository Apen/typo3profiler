<?php

$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('typo3profiler');

return array(
    'Tx_Typo3profiler_ViewHelpers_Widget_PaginateQueryViewHelper' => $extensionPath . 'Classes/ViewHelpers/Widget/PaginateQueryViewHelper.php',
    'Tx_Typo3profiler_ViewHelpers_Widget_Controller_PaginateQueryController' => $extensionPath . 'Classes/ViewHelpers/Widget/Controller/PaginateQueryController.php',
    'Tx_Typo3profiler_ViewHelpers_SpriteManagerIconViewHelper' => $extensionPath . 'Classes/ViewHelpers/SpriteManagerIconViewHelper.php'
);
