<?php

namespace OCA\OJSXC\AppInfo;

use OCA\OJSXC\Controller\HttpBindController;
use OCA\OJSXC\Db\MessageMapper;
use OCA\OJSXC\Db\StanzaMapper;
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
				$c->query('OCP\ISession'),
				$c->query('MessageMapper'),
				$c->query('StanzaMapper'),
				$c->query('Host')
			);
		});

		/**
		 * Database Layer
		 */
		$container->registerService('MessageMapper', function($c) {
			return new MessageMapper($c->query('ServerContainer')->getDb());
		});

		$container->registerService('StanzaMapper', function($c) {
			return new StanzaMapper($c->query('ServerContainer')->getDb());
		});

		/**
		 * Config values
		 */

		$container->registerService('Host', function($c){
			return $c->query('Request')->getServerHost();
		});

	}
	
}