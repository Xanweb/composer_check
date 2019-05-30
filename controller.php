<?php
namespace Concrete\Package\ComposerCheck;

use Package;
use Concrete\Core\Job\Job;
use Database;
use AssetList;
use Asb\ComposerCheck\Utility\Installer;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends Package
{

    protected $pkgHandle = 'composer_check';
    protected $appVersionRequired = '8.1.0';
    protected $pkgVersion = '1.0';
    protected $oLogger;

    protected $pkgAutoloaderRegistries = array(
        "src/Asb/ComposerCheck" => '\Asb\ComposerCheck',
        "src/Concrete" => '\Concrete\Package\ComposerCheck',
    );

    public function getPackageName()
    {
        return t("Composer check");
    }

    public function getPackageDescription()
    {
        return t("Check block if they exist in composer and/or fix them");
    }


    public function install()
    {

        parent::install();

        Installer::installSinglePages([
            ['/dashboard/composerCheck', 'Composer check'],
        ]);


    }

    public function uninstall(){
        parent::uninstall();
    }


    public function on_start()
    {
        App::setupAlias();
    }
}
?>