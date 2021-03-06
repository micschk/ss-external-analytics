<?php namespace Milkyway\SS\ExternalAnalytics\Drivers\Core;
/**
 * Milkyway Multimedia
 * SocialInteractions.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\ExternalAnalytics\Drivers\Contracts\Driver as DriverContract;
use Milkyway\SS\ExternalAnalytics\Drivers\Contracts\ScriptAttribute;
use ViewableData;

class SocialInteractions implements ScriptAttribute {
    public function output(DriverContract $driver, $id, ViewableData $controller = null, $params = []) {
	    singleton('assets')->utilities_js();
        singleton('assets')->javascript(SS_EXTERNAL_ANALYTICS_DIR . '/javascript/social-tracker.js');
        return '';
    }
} 