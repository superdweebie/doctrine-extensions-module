<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

interface ManifestAwareInterface
{

    public function setManifestName($manifestName);

    public function getManifestName();

    public function setManifestConfig(array $manifestConfig);

    public function getManifestConfig();
}
