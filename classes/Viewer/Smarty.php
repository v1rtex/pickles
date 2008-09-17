<?php

class Viewer_Smarty extends Viewer_Common {

	public function display() {
		// Obliterates any passed in PHPSESSID (thanks Google)
		if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') !== false) {
			list($request_uri, $phpsessid) = split('\?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $request_uri);
			exit();
		}

		// XHTML compliancy stuff
		ini_set('arg_separator.output', '&amp;');
		ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

		// @todo Create a wrapper so that we can auto load this
		require_once 'contrib/smarty/libs/Smarty.class.php';

		$smarty = new Smarty();

		// @todo Perhaps the templates directory would be better suited as a config variable?
		$smarty->template_dir = '../templates/';

		// @todo instead of having this in /tmp (which is Linux-scentric) perhaps move it to a folder in the common dir
		$cache_dir   = TEMP_PATH . 'cache';
		$compile_dir = TEMP_PATH . 'compile';

		if (!file_exists($cache_dir))   { mkdir($cache_dir,   0777, true); }
		if (!file_exists($compile_dir)) { mkdir($compile_dir, 0777, true); }

		$smarty->cache_dir   = $cache_dir ;
		$smarty->compile_dir = $compile_dir;

		$smarty->load_filter('output','trimwhitespace');

		// Include custom Smarty functions
		// @todo Stupid fucking hard coded path
		$directory = PICKLES_PATH . 'smarty/functions/';

		if (is_dir($directory)) {
			if ($handle = opendir($directory)) {
				while (($file = readdir($handle)) !== false) {
					if (!preg_match('/^\./', $file)) {
						list($type, $name, $ext) = split('\.', $file);
						require_once $directory . $file;
						$smarty->register_function($name, "smarty_{$type}_{$name}");
					}
				}
				closedir($handle);
			}
		}

		$navigation = $this->config->get('navigation', 'sections');

		// Add the admin section if we're authenticated
		// @todo add code to check if the user is logged in
		if (false) {
			if ($this->config->get('admin', 'menu') == true) {
				$navigation['admin'] = 'Admin';
			}
		}

		$template        = '../templates/' . $this->model->get('name') . '.tpl';
		$shared_template = str_replace('../', '../../pickles/', $template);

		if (!file_exists($template)) {
			if (file_exists($shared_template)) {
				$template = $shared_template;
			}
		}

		// Pass all of our controller values to Smarty
		$smarty->assign('navigation', $navigation);
		$smarty->assign('section',    $this->model->get('section'));
		$smarty->assign('model',      $this->model->get('name'));
		$smarty->assign('action',     $this->model->get('action')); // @todo rename me to event...
		$smarty->assign('event',      $this->model->get('action')); //       but it almost seems like we don't need these anymore at all

		// Thanks to new naming conventions
		$smarty->assign('admin',      $this->config->get('admin', 'sections'));
		$smarty->assign('template',   $template);

		// Only load the session if it's available
		// @todo not entirely sure that the view needs full access to the session (seems insecure at best)
		/*
		if (isset($_SESSION)) {
			$smarty->assign('session', $_SESSION);
		}
		*/

		$data = $this->model->getData();

		if (isset($data) && is_array($data)) {
			foreach ($data as $variable => $value) {
				$smarty->assign($variable, $value);
			}
		}

		/*
		@todo there's no error checking for the index... should it be 
		      shared, and should the error checking occur anyway since 
			  any shit could happen?

		$template        = '../templates/index.tpl';
		$shared_template = str_replace('../', '../../pickles/', $template);

		if (!file_exists($template)) {
			if (file_exists($shared_template)) {
				$template = $shared_template;
			}
		}
		*/

		// Load it up!
		header('Content-type: text/html; charset=UTF-8');
		$smarty->display('index.tpl');
	}

}

?>
