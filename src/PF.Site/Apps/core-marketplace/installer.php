<?php
$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new \Apps\Core_Marketplace\Installation\Version\v460())->process();
});