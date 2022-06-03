<?php

interface BonesInterface {
    public static function get_instance();
    public static function register($route, $callback, $method, $cnxdb, $js=[], $options=[]);
    public function getContent();
    public function getJSContent();
    public function make_route($path = '');
    public function set($index, $value); 
	public function form($key);
    public function request($key); 
	public function render($view, $layout = "layout");
	public function display_flash($variable = 'error');
	public function redirect($path = '');
}

abstract class microFmw {

    public static function get($route, $callback, $cnxdb, $js=[], $options=[]) { 
        Bones::register($route, $callback, 'GET', $cnxdb, $js, $options);
    }
    
    public static function post($route, $callback, $cnxdb, $js=[], $options=[]) { 
        Bones::register($route, $callback, 'POST', $cnxdb, $js, $options);
    }
    
    public static function put($route, $callback, $cnxdb, $js=[], $options=[]) { 
        Bones::register($route, $callback, 'PUT', $cnxdb, $js, $options);
    }
    
    public static function delete($route, $callback, $cnxdb, $js=[], $options=[]) { 
        Bones::register($route, $callback, 'DELETE', $cnxdb, $js, $options);
    }

}

/**
 * Class Bones
 * https://github.com/timjuravich/bones
 * https://www.packtpub.com/product/web-development/9781849513586
 */

class Bones implements BonesInterface {
	private static $instance;
    public static $route_found = false;
	public static $rendered = false;
    public $route = '';
    public $method = '';
	private $content = '';
    private $jscontent = [];
    public $vars = array();
	public $route_segments = array();
    public $route_variables = array();
    public static $cnxdb = null;
    public static $options = [];

    public function __construct() {
        $this->route = $this->get_route();
        $this->route_segments = explode('/', trim($this->route, '/'));
        $this->method = $this->get_method();
    }

    public static function get_instance() {
        if (!isset(self::$instance)) {
			self::$instance = new Bones();
        }
        
        return self::$instance;
    }

    protected function getDB () {
        return static::$cnxdb;
    }

    protected function getServers () {
        if (array_key_exists('servers', static::$options )) {
            return static::$options['servers'];
        } else {
            return [];
        }
        
    }

    public static function register($route, $callback, $method, $cnxdb, $js=[], $options=[]) { 
        static::$cnxdb = $cnxdb;
        static::$options = $options;
		if (!static::$route_found) {
			$bones = static::get_instance();
            $url_parts = explode('/', trim($route, '/'));
            $matched = null;
            $bones->jscontent = $js; 

            if (count($bones->route_segments) == count($url_parts)) {
                foreach ($url_parts as $key=>$part) {
                    if (strpos($part, ":") !== false) {
						// Contains a route variable
                        $bones->route_variables[substr($part, 1)] = $bones->route_segments[$key];
                    } else {
						// Does not contain a route variable
                        if ($part == $bones->route_segments[$key]) {
                            if (!$matched) {
								// Routes match
                                $matched = true;
                            }
                        } else {
							// Routes don't match
                            $matched = false;
                        }
                    }
                }
            } else {
				// Routes are different lengths
                $matched = false;
            }


            if (!$matched || $bones->method != $method) {
                return false;
            } else {
                static::$route_found = true;
                echo $callback($bones);
            }
		}
    }

    protected function get_route() {
		parse_str($_SERVER['QUERY_STRING'], $route);
		if ($route) {
        	return '/' . $route['request'];
		} else {
			return '/';
		}
    }

    public function getContent() {
        return $this->content;
    }

    public function getJSContent() {
        if (count($this->jscontent) == 0) {
            return '';
        } else {
            return implode(PHP_EOL, $this->jscontent).PHP_EOL;
        }        
    }

    public function make_route($path = '') {
        $url = explode("/", $_SERVER['PHP_SELF']);
        return '/' . $url[1] . '/' . $path;
    }

    protected function get_method() {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    public function set($index, $value) {
        $this->vars[$index] = $value;
    }

	public function form($key) {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        } else {
            return '';
        }

	}
	
    public function request($key) {
        if (array_key_exists($key, $this->route_variables)) {
            return $this->route_variables[$key];
        } else {
            return '';
        }
    }

	public function render($view, $layout = "layout") {
		if (!static::$rendered) {
			static::$rendered = true;
			$this->content = ROOT. '/views/' . $view . '.php';
	        foreach ($this->vars as $key => $value) {
	            $$key = $value;
	        }
	        include(ROOT. '/views/' . $layout . '.php');
		}
    }

	public function display_flash($variable = 'error') {
		if (isset($this->vars[$variable])) {
			return "<div class='alert-holder'><div class='alert-message " . $variable . "'><p>" . $this->vars[$variable] . "</p></div></div>";
		}
	}
	
	public function redirect($path = '') {
		header('Location: ' . $this->make_route($path));
	}

}
