<?php

use NITSAN\NsHeadlessMask\Utility\MaskElementsUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
defined('TYPO3') or die();

$maskUtility = GeneralUtility::makeInstance(MaskElementsUtility::class);
$tsComponents = $maskUtility->setupComponentWiseTypoScript();

    ExtensionManagementUtility::addTypoScript(
        'ns_headless_mask',
        'setup',
        "
        tt_content {
            $tsComponents
        }
    "
    );