<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Sds\DoctrineExtensions\Manifest;

trait ManifestAwareTrait
{

    protected $manifestName;

    protected $manifest;

    public function setManifestName($manifestName)
    {
        $this->manifestName = $manifestName;
    }

    public function getManifestName()
    {
        return $this->manifestName;
    }

    public function setManifest(Manifest $manifest)
    {
        $this->manifest = $manifest;
    }

    public function getManifest()
    {
        return $this->manifest;
    }
}
