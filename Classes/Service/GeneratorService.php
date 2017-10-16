<?php
namespace Flownative\Neos\MultisiteKickstarter\Service;

/*
 * This file is part of the Flownative.Neos.MultisiteKickstarter package.
 *
 * (c) Karsten Dambekalns, Flownative GmbH - www.flownative.com
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManagerInterface;

/**
 * Service to generate site packages
 */
class GeneratorService extends \Neos\SiteKickstarter\Service\GeneratorService
{
    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * Generate a site package and fill it with boilerplate data.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return array
     */
    public function generateMultisitePackage($packageKey, $siteName)
    {
        // below copied from parent::generateSitePackage()
        $this->packageManager->createPackage($packageKey, [
            'type' => 'neos-site',
            "require" => [
                "neos/neos" => "*",
                "neos/nodetypes" => "*",
                // we need to add these dependencies:
                "flownative/neos-multisitehelper" => "*",
                "wwwision/assetconstraints" => "*"
            ],
            "suggest" => [
                "neos/seo" => "*"
            ]
        ]);
        $this->generateSitesXml($packageKey, $siteName);
        $this->generateSitesFusion($packageKey, $siteName);
        $this->generateDefaultTemplate($packageKey, $siteName);
        $this->generateNodeTypesConfiguration($packageKey);
        $this->generateAdditionalFolders($packageKey);
        // above copied from parent::generateSitePackage()

        $this->generatePolicy($packageKey);

        return $this->generatedFiles;
    }

    /**
     * Generate additional folders for site packages.
     *
     * @param string $packageKey
     */
    protected function generatePolicy($packageKey)
    {
        $templatePathAndFilename = 'resource://Flownative.Neos.MultisiteKickstarter/Private/Generator/Configuration/Policy.yaml';

        $contextVariables = array();
        $contextVariables['packageKey'] = $packageKey;
        $packageKeyDomainPart = substr(strrchr($packageKey, '.'), 1) ?: $packageKey;
        $contextVariables['siteNodeName'] = strtolower($packageKeyDomainPart);

        $fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

        $sitePolicyPathAndFilename = $this->packageManager->getPackage($packageKey)->getConfigurationPath() . 'Policy.yaml';
        $this->generateFile($sitePolicyPathAndFilename, $fileContent);
    }
}
