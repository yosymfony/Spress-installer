<?php

namespace Yosymfony\Spress\Composer\Installer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Symfony\Component\Yaml\Yaml;

class Installer extends LibraryInstaller
{
    const TYPE_PLUGIN = 'spress-plugin';
    const TYPE_THEME = 'spress-theme';
    
    const CONFIG_FILE = 'config.yml';
    const TEMPLATE_DIR = 'app/templates';
    
    /**
    * {@inheritDoc}
    */
    public function getPackageBasePath(PackageInterface $package)
    {
        switch($package->getType())
        {
            case self::TYPE_PLUGIN:
                $dir = $this->getPluginsDir();
            break;
            
            case self::TYPE_THEME:
                $dir = $this->getThemeDir();
            break;
        }
        
        $name  = $this->getExtraName($package);
    
        return $dir . '/' . $name;
    }
    
    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return in_array($packageType, [
            self::TYPE_PLUGIN,
            self::TYPE_THEME
        ], true);
    }
    
    /**
    * Get the theme/plugin name from the package extra info
    *
    * @param PackageInterface $package
    * @throws \InvalidArgumentException
    *
    * @return string
    */
    protected function getExtraName(PackageInterface $package)
    {
        $extraData = $package->getExtra();
        
        if (!array_key_exists('spress_name', $extraData)) {
            throw new \InvalidArgumentException(
                'Unable to install theme/plugin, Spress addons must '
                .'include the name in the extra field of composer.json.'
            );
        }
        
        return $extraData['spress_name'];
    }
    
    /**
     * Get the plugins folder from config.yml of the site
     * 
     * @return string Relative path to composer.json.
     */
    protected function getPluginsDir()
    {
        $config = $this->getConfigSite();
        $relativePath = isset($config['plugins']) ? $config['plugins'] : '_plugins';
        $relativePath = $this->prepareDir($relativePath);
        
        return $relativePath;
    }
    
    /**
     * Read the config.yml of the site
     * 
     * @return array
     */
    protected function getConfigSite()
    {
        if(!file_exists(self::CONFIG_FILE))
        {
            throw new \InvalidArgumentException(
                'Unable to install Spress plugin: config.yml file of the site '
                .'was not found.'
            );
        }
        
        return Yaml::parse(self::CONFIG_FILE);
    }
    
    /**
     * Get the theme dir
     * 
     * @return string Relative path to composer.json.
     */
    protected function getThemeDir()
    {
        if(false == $this->existsTemplateDir())
        {
            throw new \InvalidArgumentException(
                sprintf(
                    "Unable to install theme. Spress themes should be installed at %s from the Spress root.", 
                    self::TEMPLATE_DIR)
            );
        }
        
        return self::TEMPLATE_DIR;
    }
    
    /**
     * Exists the template dir at the Spress installation dir?
     * 
     * @return boolean
     */
    protected function existsTemplateDir()
    {
        return file_exists(self::TEMPLATE_DIR);
    }
    
    /**
     * @param string $relativePath
     * 
     * @return string
     */
    protected function prepareDir($relativePath)
    {
        $result = str_replace(['.', '..'], '', $relativePath);
        $result = trim($result, '//');
        
        return $result;
    }
}