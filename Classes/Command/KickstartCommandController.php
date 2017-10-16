<?php
namespace Flownative\Neos\MultisiteKickstarter\Command;

/*
 * This file is part of the Flownative.Neos.MultisiteKickstarter package.
 *
 * (c) Karsten Dambekalns, Flownative GmbH - www.flownative.com
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flownative\Neos\MultisiteKickstarter\Service\GeneratorService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Package\PackageManagerInterface;

/**
 * Command controller for the MultisiteKickstarter
 */
class KickstartCommandController extends CommandController
{
    /**
     * @var PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @var GeneratorService
     * @Flow\Inject
     */
    protected $generatorService;

    /**
     * Kickstart a new multisite package
     *
     * This command generates a new site package with basic Fusion and Sites.xml,
     * prepared for use in a multi-site setup.
     *
     * @param string $packageKey The packageKey for your site
     * @param string $siteName The siteName of your site
     * @return string
     */
    public function multisiteCommand($packageKey, $siteName)
    {
        if (!$this->packageManager->isPackageKeyValid($packageKey)) {
            $this->outputLine('Package key "%s" is not valid. Only UpperCamelCase in the format "Vendor.PackageKey", please!', [$packageKey]);
            $this->quit(1);
        }

        if ($this->packageManager->isPackageAvailable($packageKey)) {
            $this->outputLine('Package "%s" already exists.', [$packageKey]);
            $this->quit(1);
        }

        $generatedFiles = $this->generatorService->generateMultisitePackage($packageKey, $siteName);

        $packageKeyDomainPart = substr(strrchr($packageKey, '.'), 1) ?: $packageKey;
        $siteNodeName = strtolower($packageKeyDomainPart);

        $this->outputLine(implode(PHP_EOL, $generatedFiles));
        $this->outputLine();
        $this->outputLine('Run "./flow site:import --package-key %s" to import the site.', [$packageKey]);
        $this->outputLine('Then run "./flow multisite:setup --site-node-name %s" to set up the site requirements.', [$siteNodeName]);
        $this->outputLine();
        $this->outputLine('Access to the site can be granted using the role "%s:%s".', [$packageKey, ucfirst($siteNodeName)]);
    }
}
