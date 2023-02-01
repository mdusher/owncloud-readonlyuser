<?php
namespace OCA\ReadOnlyUser\AppInfo;

use OCP\AppFramework\App;

class Application extends App {

    public function __construct(array $urlParams=array()) {
        parent::__construct('readonlyuser', $urlParams);
    }

    public function setupWrapper() {
        \OCP\Util::connectHook('OC_Filesystem', 'preSetup', '\OCA\ReadOnlyUser\Storage\ReadOnlyWrapper', 'setupWrapper');
    }
}
