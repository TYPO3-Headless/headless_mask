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

        //Method to call prepared the elements TypoScript...
        $tsComponents = $maskUtility->setupComponentWiseTypoScript();

        $typoScriptFileDir = Environment::getPublicPath() . '/typo3conf/ext/ns_headless_mask/Configuration/TypoScript/';
        if (Environment::isComposerMode()) {
            $typoScriptFileDir = Environment::getProjectPath() . '/vendor/nitsan/ns-headless-mask/Configuration/TypoScript/';
        }

        //Check if directory is available or not, if not then it will be created
        if (!file_exists($typoScriptFileDir)) {
            GeneralUtility::mkdir_deep($typoScriptFileDir);
        }

        $typoScriptFilePath = $typoScriptFileDir.'setup.typoscript';

        //File created...
        GeneralUtility::writeFile($typoScriptFilePath, $tsComponents);
        return Command::SUCCESS;
    }
}