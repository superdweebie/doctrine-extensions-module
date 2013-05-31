<?php

namespace Sds\DoctrineExtensionsModule\Cache;

use Doctrine\Common\Cache\FileCache;

/**
 * Caches objects as php files (which can be optimized by an optcode cache)
 * similar to PhpFileCache. However, objects are serialized, so don't need to
 * support var_export and __set_state, as required by PhpFileCache.
 *
 * @author Tim Roediger <superdweebie@gmail.com>
 */
class PhpFileSerializeCache extends FileCache
{
    const EXTENSION = '.php';

    /**
     * {@inheritdoc}
     */
    protected $extension = self::EXTENSION;

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {

        $filename = $this->getFilename($id);
        $value = @include $filename;
        if (!isset($value)){
            return false;
        }

        $lifetime = (integer) $value['lifetime'];
        if ($lifetime !== 0 && $lifetime < time()) {
            return false;
        }
        return unserialize($value['data']);
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        $filename = $this->getFilename($id);
        $value = @include $filename;
        if (!isset($value)){
            return false;
        }

        $lifetime = $value['lifetime'];

        return $lifetime === 0 || $lifetime > time();
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifetime = 0)
    {
        if ($lifetime > 0) {
            $lifetime = time() + $lifetime;
        }

        $filename   = $this->getFilename($id);
        $filepath   = pathinfo($filename, PATHINFO_DIRNAME);

        if ( ! is_dir($filepath)) {
            mkdir($filepath, 0777, true);
        }

        $value = [
            'lifetime' => $lifetime,
            'format' => 'standard',
            'data' => serialize($data)
        ];
        return file_put_contents($filename, sprintf('<?php return %s;', var_export($value, true)));
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function getFilename($id)
    {
        $hash = hash('sha256', $id);
        $path = implode(str_split($hash, 16), DIRECTORY_SEPARATOR);
        $path = $this->directory . DIRECTORY_SEPARATOR . $path;
        $id   = preg_replace('@[\\\/:"*?<>|]+@', '', $id);
        $id   = substr($id, -50); //hack so that path names aren't too long for old OS's
        return $path . DIRECTORY_SEPARATOR . $id . $this->extension;
    }
}
