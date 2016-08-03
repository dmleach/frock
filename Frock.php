<?php

namespace dmleach\frock;

class Frock
{
    /** @var string $pathVariable The key in the http request that contains
            the requested path. This must match the variable declared in the
            rewrite rule in .htaccess */
    private $pathKey = 'path';

    /** @var string $path The requested path */
    private $path = '';

    /** @var string $defaultPath The path used by default if any of the path-
            related functions are called with a null parameter */
    public $defaultPath = 'hello';

    /** @var string $baseNamespace The namespace that contains the controllers
            this front controller will reference */
    public $baseNamespace = '';
    private $controllerNamespace = 'controller';

    /** @var bool $debug If true, messages are written to the debug log */
    public $debug = false;

    /** @var string[] $debugLog An array of debug messages */
    public $debugLog = [];


    /**
     * @param array $request An http request array. If null, $_REQUEST is used
     */
    public function __construct($request = null)
    {
        $this->logMessage("__construct: request = " . print_r($request));

        $request = is_null($request) ? $_REQUEST : $request;
        $this->processHttpRequest($request);
    }

    /**
     * Instantiates the controller at the given path and runs its execute method
     *
     * @param string $path The controller's request path. If null, the default
     *     controller is used
     *
     * @return void
     */
    public function executePathController($path = null)
    {
        $this->logMessage("executePathController: path = {$path}");

        $controller = $this->instantiatePathController($path);
        $controller->execute();
    }

    /**
     * Converts a request path into a namespace and controller class name
     *
     * @param string $path The controller's request path. If null, the default
     *     controller is used
     *
     * @return string
     */
    public function getControllerClassName($path)
    {
        $this->logMessage("getControllerClassName: path = {$path}");

        if (is_null($path)) {
            $path = is_null($this->path) ? $this->defaultPath : $this->path;
        }

        // Convert any slashes in the path to namespace separators
        $path = str_replace('/', '\\', $path);

        $classNamespace = $this->baseNamespace . '\\' . $this->controllerNamespace;
        $className = $classNamespace . '\\' . $path;

        // PSR-1 specifies the class name must be capitalized
        $lastBackslashPos = strrpos($className, '\\');

        if ($lastBackslashPos !== false) {
            $className[$lastBackslashPos + 1] = strtoupper($className[$lastBackslashPos + 1]);
        }

        return $className;
    }

    /**
     * Returns the value of the private path variable
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the value of the private path key variable
     *
     * @return string
     */
    public function getPathKey()
    {
        return $this->pathKey;
    }

    /**
     * Instantiates and returns the controller at the given path
     *
     * @param string $path The controller's request path. If null, the default
     *     controller is used
     *
     * @return object
     */
    public function instantiatePathController($path = null)
    {
        $this->logMessage("instantiatePathController: path = {$path}");

        $className = $this->getControllerClassName($path);
        $this->logMessage("instantiatePathController: className = {$className}");

        if (class_exists($className)) {
            $controller = new $className;
            return $controller;
        } else {
            throw new \Exception("Class not found: {$className}");
        }
    }

    protected function logMessage($message)
    {
        if ($this->debug) {
            $this->debugLog[] = $message;
        }
    }

    /**
     * Extracts values from an http request
     *
     * @param array $request An http request array
     *
     * @return bool True if the path value was successfully set; false otherwise
     */
    public function processHttpRequest($request)
    {
        $this->logMessage('processHttpRequest: request = ' . print_r($request, true));

        $this->path = null;

        if (is_array($request)) {
            $this->path = array_key_exists($this->pathKey, $request)
                ? $request[$this->pathKey]
                : null;
        }

        return !is_null($this->path);
    }

    /**
     * Sets and validates the private path key variable
     *
     * @param string|int $key The new path key value
     *
     * @return book True if the given key was valid; false otherwise
     */
    public function setPathKey($key)
    {
        $this->logMessage("setPathKey: key = {$key}");

        if (is_string($key) || is_int($key)) {
            $this->pathKey = $key;
            return true;
        } else {
            return false;
        }
    }
}
