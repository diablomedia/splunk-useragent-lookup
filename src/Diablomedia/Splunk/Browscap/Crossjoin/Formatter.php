<?php

namespace Diablomedia\Splunk\Browscap\Crossjoin;

use Crossjoin\Browscap\Formatter\AbstractFormatter;
use stdClass;

class Formatter extends AbstractFormatter
{
    public function __construct()
    {
        $this->settings = new stdClass();
    }

    /**
     * Sets the data (done by the parser).
     *
     * @param array $settings
     */
    public function setData(array $settings)
    {
        $this->settings = new stdClass();
        foreach ($settings as $key => $value) {
            $key = 'ua_' . strtolower($key);
            $this->settings->$key = $value;
        }

        $this->settings->ua_litemode = 'false';

        if (!isset($this->settings->ua_masterparent)) {
            $this->settings->ua_masterparent = 'false';
        }

        $this->settings->ua_propertyname = $this->settings->ua_browser_name_pattern;
        unset($this->settings->ua_browser_name_pattern);
    }

    /**
     * Gets the data (in the preferred format).
     *
     * @return \stdClass
     */
    public function getData()
    {
        return $this->settings;
    }
}
