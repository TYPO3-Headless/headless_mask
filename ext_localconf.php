<?php
defined('TYPO3') or die();

call_user_func(function()
{
    $extensionKey = 'ns_headless_mask';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        $extensionKey,
        'setup',
        "@import 'EXT:ns_headless_mask/Configuration/TypoScript/setup.typoscript'"
    );
});