<?php

namespace Eventzapp;

class TemplateHelper {

	const ENGINE_CLASS = 'Smarty';
	private $engine;
	private $templateFile;
	private $pluginsDirectories = array( 'vendor/smarty-gettext/smarty-gettext' );

	public function __construct($dir) {
		try {
			$classname = TemplateHelper::ENGINE_CLASS;
			$this->engine = new $classname();
		}
		catch(SmartyCompilerException $exception) {
			Debugger::log($exception->getMessage());
		}

		if(!is_a($this->engine, TemplateHelper::ENGINE_CLASS)) {
			return Debugger::log('Smarty class could not be loaded');
		}

		$this->engine->setTemplateDir(getenv('TEMPLATE_DIR'));
		$this->engine->setCompileDir(getenv('COMPILE_DIR'));
		$this->engine->setCacheDir(getenv('CACHE_DIR'));

		if(method_exists($this->engine, 'setConfigDir')) {
			$this->engine->setConfigDir(getenv('CONFIGS_DIR'));
		}

		foreach ($this->pluginsDirectories as $pluginsDirectory) {
			$pluginsDirectory = $dir . DIRECTORY_SEPARATOR . $pluginsDirectory;
			if(is_dir($pluginsDirectory)) {
				$this->engine->addPluginsDir($pluginsDirectory);
			}
		}

		if(method_exists($this->engine, 'assign')) {
			$this->engine->assign('app_id', getenv('APP_ID'));
			$this->engine->assign('app_url', getenv('APP_URL'));
		}
	}

	public function assign($name, $value) {
		$this->engine->assign($name, $value);
	}

	public function setTemplateFile($templateFile) {
		return ($this->templateFile = $templateFile);
	}

	public function addPluginsDir($dir) {
		$this->engine->addPluginsDir($dir);
	}

	public function getPluginsDir() {
		if(method_exists($this->engine, 'getPluginsDir')) {
			return $this->engine->getPluginsDir();
		}
	}

	public function getEngine() {
		return $this->engine;
	}

	public function display() {
		try {
			if(is_a($this->engine, TemplateHelper::ENGINE_CLASS) && !empty($this->templateFile)) {
				$this->engine->display($this->templateFile);
			}
		}
		catch(Exception $exception) {
			return ''; // Because __toString can't throw exceptions
		}
	}

}
