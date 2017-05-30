<?php
namespace Metaphore\Store;

use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;

/**
 * Think again if you really truly want to use filesystem for caching any data ;-)
 */
class FilePhpStore implements ValueStoreInterface, LockStoreInterface
{
    /**
     * @var string
     */
    protected $directory;

    protected static $strDenyAccess =  '<?php return header("HTTP/1.0 404 Not Found"); ?>'; //deny access from web-browser

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
        $data = self::$strDenyAccess . '|' . $ttl . '|' . serialize($value);

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
        list($strDenyAccess, $ttl, $serializedValue) = explode('|', $data, 3);

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
        $fileName = $this->getFileName($key);

        if (file_exists($fileName)) {
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
        return $this->directory . DIRECTORY_SEPARATOR . $key . '.php';
    }
}
