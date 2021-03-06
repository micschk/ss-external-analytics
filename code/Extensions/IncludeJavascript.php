<?php namespace Milkyway\SS\ExternalAnalytics\Extensions;
/**
 * Milkyway Multimedia
 * Logger.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Flushable;
use Extension;
use Requirements;
use SS_Cache;
use Milkyway\SS\Director;

class IncludeJavascript extends Extension implements Flushable {
	protected static $controller;
	protected static $currentExtension;
	protected $cache;

	public function onAfterInit() {
		static::$controller = $this->owner;
		static::$currentExtension = $this;

		if(!singleton('env')->get('ExternalAnalytics.include_js_after_action_handled')) {
			$this->js();
		}
	}

	public function afterCallActionHandler() {
		$this->onAfterInit();
		if(singleton('env')->get('ExternalAnalytics.include_js_after_action_handled')) {
			$this->js();
		}
	}

//	public function beforeProcessHtml($event, $template) {
//		\debug::show($template);
//		$this->js();
//	}
//
//	public function beforeProcessResponse() {
//		$this->js();
//	}

	protected function js() {
		if(!static::$currentExtension || !static::$controller)
			return;

		static::$currentExtension->setOwner(static::$controller);

		$self = static::$currentExtension;
		$request = $self->owner->Request;
		$params = array_merge(
			['SessionLink' => singleton('Milkyway\SS\ExternalAnalytics\Controller')->Link()],
			(array)$self->owner->ExternalAnalyticsParams
		);

		singleton('ea')->executeDrivers(function($driver, $id) use($self, $params, $request) {
			$script = '';

			$cacheKey = $this->obtainCacheKey([
				'url' => $request ? $request->getUrl(true) : '?',
				'driver' => get_class($driver),
				'id' => $id . '-script',
			]);

			if(Director::isDev() || ($request && !Director::isDev() && !($script = $this->cache()->load($cacheKey)))) {
				if($script = $driver->javascript($id, $self->owner, $params)) {
					if($request && !Director::isDev()) {
						require_once(THIRDPARTY_PATH . DIRECTORY_SEPARATOR .'jsmin' . DIRECTORY_SEPARATOR . 'jsmin.php');
						increase_time_limit_to();
						$script = \JSMin::minify($script);
						$this->cache()->save($script, $cacheKey);
					}
				}
			}

			if($script)
				Requirements::insertHeadTags('<script type="text/javascript">' . $script . '</script>', $id . '-script');
		});
	}

	public static function flush() {
		singleton(__CLASS__)->cache()->clean();
	}

	public function cache() {
		if(!$this->cache)
			$this->cache = SS_Cache::factory('Milkyway_SS_ExternalAnalytics_Extensions_IncludeJavascript', 'Output', ['lifetime' => 20000 * 60 * 60]);

		return $this->cache;
	}

	protected function obtainCacheKey(array $vars = []) {
		return preg_replace('/[^a-zA-Z0-9_]/', '', get_class($this) . '_' . urldecode(http_build_query($vars, '', '_')));
	}
}