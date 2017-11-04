<?php
namespace Metaphore\Store;

use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;

/**
 * Think again if you really truly want to use filesystem for caching any data ;-)
 */
class FileStore implements ValueStoreInterface, LockStoreInterface
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @param string
     */
    public function __construct($directory = null)
    {
        if (!$directory) {
            $directory = sys_get_temp_dir();
        }

        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl)
    {
        $fileName = $this->getFileName($key);
        $data = $ttl.'|'.serialize($value);

        return file_put_contents($fileName, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $fileName = $this->getFileName($key);

        if (!file_exists($fileName)) {
            return false;
        }

        $data = file_get_contents($fileName);
        list($ttl, $serializedValue) = explode('|', $data, 2);

        if ($ttl > time()) {
            return false;
        }

        return unserialize($serializedValue);
    }

    /**
     * {@inheritdoc}
     */
    public function add($key, $value, $ttl)
    {
        if ($this->get($key) !== false) {
            return false;
        }

        return $this->set($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $fileName = $this->getFileName($key);

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        return true;
    }

    protected function getFileName($key)
    {
        return $this->directory.DIRECTORY_SEPARATOR.$key;
    }
}
