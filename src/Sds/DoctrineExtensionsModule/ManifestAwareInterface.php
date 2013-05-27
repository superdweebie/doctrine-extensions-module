<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Sds\DoctrineExtensions\Manifest;

interface ManifestAwareInterface
{

    public function setManifestName($manifestName);

    public function getManifestName();

    public function setManifest(Manifest $manifest);

    public function getManifest();
}
