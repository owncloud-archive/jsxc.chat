<?php

namespace OCA\OJSXC\AppInfo;

use OCA\OJSXC\Controller\HttpBindController;
use OCA\OJSXC\Db\MessageMapper;
use OCA\OJSXC\Db\PresenceMapper;
use OCA\OJSXC\Db\StanzaMapper;
use OCA\OJSXC\NewContentContainer;
use OCA\OJSXC\StanzaHandlers\IQ;
use OCA\OJSXC\StanzaHandlers\Message;
use OCA\OJSXC\StanzaHandlers\Presence;
use OCP\AppFramework\App;
use OCA\OJSXC\ILock;
use OCA\OJSXC\DbLock;
use OCA\OJSXC\MemLock;
use OCP\ICache;

class Application extends App {

	private static $config = [];

	public function __construct(array $urlParams=array()){
		parent::__construct('ojsxc', $urlParams);
		$container = $this->getContainer();

		/** @var $config \OCP\IConfig */
		$configManager = $container->query('OCP\IConfig');

		self::$config['polling'] = $configManager->getSystemValue('ojsxc.polling',
			['sleep_time' => 1, 'max_cycles' => 10]);

		self::$config['polling']['timeout'] = self::$config['polling']['sleep_time'] * self::$config['polling']['max_cycles'] + 5;

		self::$config['use_memcache'] = $configManager->getSystemValue('ojsxc.use_memcache',
			['locking' => false]);


		$container->registerService('HttpBindController', function($c){
			return new HttpBindController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('UserId'),
				$c->query('StanzaMapper'),
				$c->query('IQHandler'),
				$c->query('MessageHandler'),
				$c->query('Host'),
				$this->getLock(),
				$c->query('OCP\ILogger'),
				$c->query('PresenceHandler'),
				$c->query('PresenceMapper'),
				file_get_contents("php://input"),
				self::$config['polling']['sleep_time'],
				self::$config['polling']['max_cycles'],
				$c->query('NewContentContainer')
			);
		});

		/**
		 * Database Layer
		 */
		$container->registerService('MessageMapper', function($c) {
			return new MessageMapper(
				$c->query('ServerContainer')->getDb(),
				$c->query('Host')
			);
		});

		$container->registerService('StanzaMapper', function($c) {
			return new StanzaMapper(
				$c->query('ServerContainer')->getDb(),
				$c->query('Host')
			);
		});

		$container->registerService('PresenceMapper', function($c) {
			return new PresenceMapper(
				$c->query('ServerContainer')->getDb(),
				$c->query('Host'),
				$c->query('UserId'),
				$c->query('MessageMapper'),
				$c->query('NewContentContainer'),
				self::$config['polling']['timeout']
			);
		});


		/**
		 * XMPP Stanza Handlers
		 */
		$container->registerService('IQHandler', function($c) {
			return new IQ(
				$c->query('UserId'),
				$c->query('Host'),
				$c->query('OCP\IUserManager')
			);
		});

		$container->registerService('PresenceHandler', function($c) {
			return new Presence(
				$c->query('UserId'),
				$c->query('Host'),
				$c->query('PresenceMapper'),
				$c->query('MessageMapper')
			);
		});

		$container->registerService('MessageHandler', function($c) {
			return new Message(
				$c->query('UserId'),
				$c->query('Host'),
				$c->query('MessageMapper')
			);
		});

		/**
		 * Config values
		 */
		$container->registerService('Host', function($c){
			$request = $c->query('Request');
			if (method_exists($request, 'getServerHost')) {
				return $c->query('Request')->getServerHost();
			} else {
				return $this->getServerHost();
			}
		});

		$container->registerService('NewContentContainer', function($c){
			return new NewContentContainer();
		});

	}

	/**
	 * @return ILock
	 */
	private function getLock() {
		$c = $this->getContainer();
		if (self::$config['use_memcache']['locking'] === true) {
			$cache = $c->getServer()->getMemCacheFactory();
			$version = \OC::$server->getSession()->get('OC_Version');
			if ($version[0] === 8 && $version[1] == 0){
				$c->getServer()->getLogger()->warning('OJSXC is configured to use memcache as backend for locking, but ownCloud version 8  doesn\'t suppor this.');
			} else if ($cache->isAvailable()) {
				$memcache = $cache->create('ojsxc');
				return new MemLock(
					$c->query('UserId'),
					$memcache
				);
			} else {
				$c->getServer()->getLogger()->warning('OJSXC is configured to use memcache as backend for locking, but no memcache is available.');
			}
		}

		// default
		return new DbLock(
			$c->query('UserId'),
			$c->query('OCP\IDb'),
			$c->query('OCP\IConfig')
		);

	}

	/**
	 * Helper function
	 * https://github.com/owncloud/core/blob/a977465af5834a76b1e98854a2c9bfbe413c218c/lib/private/appframework/http/request.php#L518
	 * @return string
	 */
	private function getServerHost() {
		$host = 'localhost';
		if (isset($this->server['HTTP_X_FORWARDED_HOST'])) {
			if (strpos($this->server['HTTP_X_FORWARDED_HOST'], ',') !== false) {
				$parts = explode(',', $this->server['HTTP_X_FORWARDED_HOST']);
				$host = trim(current($parts));
			} else {
				$host = $this->server['HTTP_X_FORWARDED_HOST'];
			}
		} else {
			if (isset($this->server['HTTP_HOST'])) {
				$host = $this->server['HTTP_HOST'];
			} else if (isset($this->server['SERVER_NAME'])) {
				$host = $this->server['SERVER_NAME'];
			}
		}
		if ($host !== null) {
			return $host;
		}
		// get the host from the headers
		$host = $this->getInsecureServerHost();
		// Verify that the host is a trusted domain if the trusted domains
		// are defined
		// If no trusted domain is provided the first trusted domain is returned
		$trustedDomainHelper = new TrustedDomainHelper($this->config);
		if ($trustedDomainHelper->isTrustedDomain($host)) {
			return $host;
		} else {
			$trustedList = $this->config->getSystemValue('trusted_domains', []);
			if(!empty($trustedList)) {
				return $trustedList[0];
			} else {
				return '';
			}
		}
	}
	
}