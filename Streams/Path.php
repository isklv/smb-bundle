<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Licensed under the MIT license:
 * http://opensource.org/licenses/MIT
 */

namespace SMBBundle\Streams;

/**
 * A string-like object that automatically registers a stream wrapper when used and removes the stream wrapper when no longer used
 *
 * Can optionally pass context options to the stream wrapper
 */
class Path {

	/**
	 * @var bool
	 */
	protected $registered = false;

	/**
	 * @var string
	 */
	protected $protocol;

	/**
	 * @var string
	 */
	protected $class;

	/**
	 * @var array
	 */
	protected $contextOptions;

	/**
	 * @param string $class
	 * @param array $contextOptions
	 */
	public function __construct($class, $contextOptions = array()) {
		$this->class = $class;
		$this->contextOptions = $contextOptions;
	}

	public function getProtocol() {
		if (!$this->protocol) {
			$this->protocol = 'auto' . uniqid();
		}
		return $this->protocol;
	}

	public function wrapPath($path) {
		return $this->getProtocol() . '://' . $path;
	}

	protected function register() {
		if (!$this->registered) {
			$this->appendDefaultContent($this->getProtocol(), $this->contextOptions);
			stream_wrapper_register($this->getProtocol(), $this->class);
			$this->registered = true;
		}
	}

	protected function unregister() {
		stream_wrapper_unregister($this->getProtocol());
		$this->unsetDefaultContent($this->getProtocol());
		$this->registered = false;
	}

	/**
	 * Add values to the default stream context
	 *
	 * @param string $key
	 * @param array $values
	 */
	protected function appendDefaultContent($key, $values) {
		$context = stream_context_get_default();
		$defaults = stream_context_get_options($context);
		$defaults[$key] = $values;
		stream_context_set_default($defaults);
	}

	/**
	 * Remove values from the default stream context
	 *
	 * @param string $key
	 */
	protected function unsetDefaultContent($key) {
		$context = stream_context_get_default();
		$defaults = stream_context_get_options($context);
		unset($defaults[$key]);
		stream_context_set_default($defaults);
	}

	public function __toString() {
		$this->register();
		return $this->protocol . '://';
	}

	public function __destruct() {
		$this->unregister();
	}
}
