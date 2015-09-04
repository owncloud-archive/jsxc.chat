<?php

namespace OCA\OJSXC\AppInfo;

use OCA\OJSXC\Controller\HttpBindController;
use \OCP\AppFramework\App;

class Application extends App {

	public function __construct(array $urlParams=array()){
		parent::__construct('ojsxc', $urlParams);
		$container = $this->getContainer();

		$container->registerService('HttpBindController', function($c){
			return new HttpBindController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('UserId'),
				$c->query('OCP\ISession')
			);
		});

	}
	
}