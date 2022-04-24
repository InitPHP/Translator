<?php
/**
 * Translator.php
 *
 * This file is part of InitPHP Translator.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP Translator
 * @license    http://initphp.github.io/license.txt  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Translator;

class Translator implements TranslatorInterface
{

    protected array $container = [];

    protected string $dir;

    protected string $default;

    protected string $current;

    protected bool $languagesAreDirectory = false;

    public function __construct()
    {
    }

    public function __debugInfo()
    {
        $deBug = [
            'system'    => ($this->languagesAreDirectory === FALSE ? 'file' : 'dir'),
            'default'   => $this->default ?? null,
            'current'   => $this->current ?? null,
        ];
        if(isset($this->current)){
            $deBug['container'] = $this->container[$this->current] ?? null;
        }
        return $deBug;
    }

    /**
     * @inheritDoc
     */
    public function setDir(string $dir): self
    {
        if(!is_dir($dir)){
            throw new TranslatorException("I couldn't find a directory in the (" . $dir . ") path.");
        }
        $this->dir = $dir;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDefault(string $default): self
    {
        $this->default = $default;
        if(!isset($this->container[$default])){
            $this->load($default);
        }
        if(!isset($this->current)){
            $this->current = $default;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function useFile(): self
    {
        $this->languagesAreDirectory = false;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function useDirectory(): self
    {
        $this->languagesAreDirectory = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function change(string $current): self
    {
        $this->current = $current;
        if(!isset($this->container[$current])){
            $this->load($current);
        }
        if(!isset($this->default)){
            $this->default = $current;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function _r(string $key, ?string $default = null, array $context = []): string
    {
        if(($translate = $this->translate($this->current, $key, $context)) !== null){
            return $translate;
        }
        if(!empty($default)){
            return $this->interpolate($default, $context);
        }
        if ($this->default == $this->current) {
            return $key;
        }
        if(($translate = $this->translate($this->default, $key, $context)) !== null){
            return $translate;
        }
        return $key;
    }

    /**
     * @inheritDoc
     */
    public function _e(string $key, ?string $default = null, array $context = []): void
    {
        echo $this->_r($key, $default, $context);
    }

    /**
     * @param string $language
     * @param string $key
     * @param array $context
     * @return string|null
     */
    private function translate(string $language, string $key, array $context): ?string
    {
        if(!isset($this->container[$language])){
            return null;
        }
        if(strpos($key, '.') !== FALSE){
            $parse = explode('.', $key);
            if(($r = ($this->container[$this->current][$parse[0]] ?? null)) === null){
                return null;
            }
            array_shift($parse);
            foreach ($parse as $subKey) {
                if(!isset($r[$subKey])){
                    $r = null;
                    break;
                }
                $r = $r[$subKey];
            }
        }else{
            $r = $this->container[$this->current][$key] ?? null;
        }
        if($r === null){
            return null;
        }
        return $this->interpolate($r, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function interpolate(string $message, array $context = []): string
    {
        if(empty($context)){
            return $message;
        }
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * @param string $lang
     * @return void
     * @throws TranslatorException
     */
    private function load(string $lang): void
    {
        $path = $this->dir . DIRECTORY_SEPARATOR . $lang
            . ($this->languagesAreDirectory ? DIRECTORY_SEPARATOR : '.php');
        if(!file_exists($path)){
            throw new TranslatorException('The file or directory could not be found. "' . $path . '"');
        }
        if($this->languagesAreDirectory === FALSE){
            $this->container[$lang] = $this->requireFile($path);
            return;
        }
        if(($files = glob($path . '*.php')) === FALSE){
            throw new TranslatorException('The directory could not be read or does not contain a suitable file. "' . $path . '"');
        }
        foreach ($files as $file) {
            $name = strtolower(basename($file, '.php'));
            $this->container[$lang][$name] = $this->requireFile($file);
        }
    }

    /**
     * @param string $path
     * @return array
     * @throws TranslatorException
     */
    private function requireFile(string $path): array
    {
        if(!is_file($path)){
            throw new TranslatorException('The requested file was not found. "' . $path . '"');
        }
        return require $path;
    }

}
