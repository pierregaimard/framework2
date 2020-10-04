<?php

namespace Framework3\Config;

use Framework3\Exception\AppException;
use Framework3\Filesystem\FileReader;

class ConfigContainer
{
    private const TYPE_JSON = 'json';
    private const TYPE_YAML = 'yaml';

    private const TYPES_EXT = [
        self::TYPE_JSON => '.json',
        self::TYPE_YAML => '.yaml',
    ];

    /**
     * @var FileReader
     */
    private FileReader $fileReader;

    /**
     * @var string
     */
    private string $appConfigDir;

    /**
     * @var string
     */
    private string $appConfigFileType;

    /**
     * @var array
     */
    private array $container = [];

    /**
     * ConfigContainer constructor.
     *
     * @param FileReader $fileReader
     * @param string     $appConfigDir
     * @param string     $appConfigFileType
     *
     * @throws AppException
     */
    public function __construct(FileReader $fileReader, string $appConfigDir, string $appConfigFileType)
    {
        $this->fileReader = $fileReader;
        $this->appConfigDir = $appConfigDir;
        $this->setAppConfigFileType($appConfigFileType);
    }

    /**
     * @param string $path
     * @param bool   $required
     *
     * @return ConfigBag|false
     *
     * @throws AppException
     */
    public function getConfig(string $path, bool $required = false)
    {
        if (array_key_exists($path, $this->container)) {
            return $this->container[$path];
        }

        $frameworkConfigFile = $this->loadConfig($this->getFrameworkConfigPath($path));
        $appConfigFile = $this->loadConfig($this->getAppConfigPath($path));

        if (!$frameworkConfigFile && !$appConfigFile) {
            if ($required) {
                throw new AppException(
                    AppException::TYPE_CONFIG,
                    'Required config file is missing',
                    sprintf('Required path: %s', $path)
                );
            }

            return false;
        }

        if (!$frameworkConfigFile) {
            $this->container[$path] = new ConfigBag(
                $path,
                $this->getArrayData($appConfigFile, $this->appConfigFileType)
            );
        }

        if (!$appConfigFile) {
            $this->container[$path] = new ConfigBag(
                $path,
                $this->getArrayData($frameworkConfigFile, self::TYPE_JSON)
            );
        }

        if ($frameworkConfigFile && $appConfigFile) {
            $data = array_replace_recursive(
                $this->getArrayData($appConfigFile, $this->appConfigFileType),
                $this->getArrayData($frameworkConfigFile, self::TYPE_JSON)
            );

            $this->container[$path] = new ConfigBag($path, $data);
        }

        return $this->container[$path];
    }

    /**
     * @param string $file
     * @param string $type
     *
     * @return array|null
     */
    private function getArrayData(string $file, string $type): ?array
    {
        switch ($type) {
            case self::TYPE_JSON:
                return json_decode($file, true);
            case self::TYPE_YAML:
                return yaml_parse($file);
            default:
                return null;
        }
    }

    /**
     * @param $path
     *
     * @return string|false
     */
    private function loadConfig($path)
    {
        if (!$this->fileReader->has($path)) {
            return false;
        }

        return $this->fileReader->getContent($path, FileReader::TYPE_STRING);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getFrameworkConfigPath(string $path): string
    {
        return __DIR__ . '/../../config/' . $path . '.json';
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getAppConfigPath(string $path): string
    {
        return $this->appConfigDir . '/' . $path . self::TYPES_EXT[$this->appConfigFileType];
    }

    /**
     * @param string $appConfigFileType
     *
     * @throws AppException
     */
    private function setAppConfigFileType(string $appConfigFileType): void
    {
        if (in_array($appConfigFileType, self::TYPES_EXT)) {
            throw new AppException(
                AppException::TYPE_ENV,
                sprintf('Invalid config file type declaration: "%s"', $appConfigFileType),
                'See "CONFIG_FILE_TYPE" parameter in .env file'
            );
        }

        $this->appConfigFileType = $appConfigFileType;
    }
}
