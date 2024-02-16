<?php
defined('TYPO3') or die();

call_user_func(function()
{
    $extensionKey = 'headless_mask';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        $extensionKey,
        'setup',
        "@import 'EXT:headless_mask/Configuration/TypoScript/setup.typoscript'"
    );
});