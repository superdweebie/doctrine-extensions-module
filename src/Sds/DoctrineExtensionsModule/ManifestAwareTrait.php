<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

trait ManifestAwareTrait
{

    protected $manifestName;

    protected $manifestConfig;

    public function setManifestName($manifestName)
    {
        $this->manifestName = $manifestName;
        return $this;
    }

    public function getManifestName()
    {
        return $this->manifestName;
    }

    public function setManifestConfig(array $manifestConfig)
    {
        $this->manifestConfig = $manifestConfig;
        return $this;
    }

    public function getManifestConfig()
    {
        return $this->manifestConfig;
    }
}
