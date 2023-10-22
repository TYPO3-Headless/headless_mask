<?php

namespace NITSAN\NsHeadlessMask\Command;

use NITSAN\NsHeadlessMask\Utility\MaskElementsUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GenerateElementTsCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('Generate TypoScript of each elements');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maskUtility = GeneralUtility::makeInstance(MaskElementsUtility::class);
        $tsComponents = $maskUtility->setupComponentWiseTypoScript();
        $typoScriptFilePath = Environment::getPublicPath() . '/typo3conf/ext/ns_headless_mask/Configuration/TypoScript/setup.typoscript';
        if (Environment::isComposerMode()) {
            $typoScriptFilePath = Environment::getProjectPath() . '/vendor/nitsan/ns-headless-mask/Configuration/TypoScript/setup.typoscript';
        }

        GeneralUtility::writeFile($typoScriptFilePath, $tsComponents);
        return Command::SUCCESS;
    }
}