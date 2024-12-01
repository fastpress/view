<?php

declare(strict_types=1);

namespace Fastpress\Presentation;

/**
 * View class for rendering templates and layouts.
 *
 * This class provides methods for rendering views, extending layouts,
 * defining and rendering blocks, sharing data between views, and escaping output.
 * It also implements the ArrayAccess interface for accessing configuration data.
 */
class View implements \ArrayAccess
{
    /**
     * @var object The application instance.
     */
    private object $app;

    /**
     * @var array An array to store sections of content.
     */
    private array $sections = [];

    /**
     * @var ?string The name of the current block being rendered.
     */
    private ?string $currentBlock = null;

    /**
     * @var ?string The name of the layout to use.
     */
    private ?string $layout = null;

    /**
     * @var array An array to store data shared between views.
     */
    protected array $sharedData = [];

    /**
     * @var array An array to store configuration data.
     */
    private array $config;

    /**
     * @var object $session The session object used for managing user sessions.
     */
    private object $session;

    /**
     * Constructor.
     *
     * @param object $app The application instance.
     */
    public function __construct(object $app, object $session)
    {
        $this->app = $app;
        $this->config = $app->getConfig();
        $this->session = $session;
        $this->validatePaths();
    }

    /**
     * Validates the configured template paths.
     *
     * @throws \RuntimeException If a required path is not configured or does not exist.
     */
    private function validatePaths(): void
    {
        $config = $this->app->config();

        $paths = ['views', 'layout'];
        foreach ($paths as $path) {
            if (!isset($config['template'][$path])) {
                throw new \RuntimeException("Required template path '{$path}' not configured");
            }
            if (!is_dir($config['template'][$path])) {
                throw new \RuntimeException("Template directory '{$path}' not found");
            }
        }
    }

    /**
     * Renders a view.
     *
     * @param string $view The name of the view file.
     * @param array $data An array of data to pass to the view.
     * @return self
     * @throws \RuntimeException If the view file is not found.
     */
    public function render(string $view, array $data = []): self
    {
        try {
            // Start output buffering
            ob_start();
            
            // Extract passed data
            extract($data, EXTR_SKIP);
            
            // Extract core variables for templates
            $app = $this;  // This makes $app available in templates
            
            // Include the view
            $viewPath = $this->config['template']['views'] . '/' . $view;
            if (!file_exists($viewPath)) {
                throw new \RuntimeException("View not found: {$view}");
            }
            
            require $viewPath;
            $content = ob_get_clean();

            // If layout is set, render it
            if ($this->layout) {
                $layoutPath = $this->config['template']['layout'] . '/' . $this->layout . '.html';
                if (!file_exists($layoutPath)) {
                    throw new \RuntimeException("Layout not found: {$this->layout}");
                }
                require $layoutPath;
            } else {
                echo $content;
            }

        } catch (\Throwable $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

        return $this;
    }

    /**
     * Sets the layout to use.
     *
     * @param string $layout The name of the layout file.
     * @return self
     */
    public function extend(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Starts a block.
     *
     * @param string $name The name of the block.
     * @return self
     */
    public function block(string $name): self
    {
        $this->currentBlock = $name;
        ob_start();
        return $this;
    }

    /**
     * Ends a block.
     *
     * @param string|null $name The name of the block to end.
     * @throws \RuntimeException If the block name is mismatched or no block is started.
     */
    public function endBlock(string $name = null): void
    {
        if ($name !== null && $name !== $this->currentBlock) {
            throw new \RuntimeException('Mismatched block name');
        }

        if ($this->currentBlock === null) {
            throw new \RuntimeException('No block started');
        }

        $this->sections[$this->currentBlock] = ob_get_clean();
        $this->currentBlock = null;
    }

    /**
     * Renders the content of a block.
     *
     * @param string $name The name of the block.
     */
    public function content(string $name): void
    {
        echo $this->sections[$name] ?? '';
    }

    /**
     * Gets a configuration value.
     *
     * @param string|null $path The path to the configuration value.
     * @return mixed The configuration value, or null if not found.
     */
    public function config(string $path = null): mixed
    {
        if ($path === null) {
            return $this->config;
        }

        $parts = explode('.', $path);
        $data = $this->config;

        foreach ($parts as $part) {
            if (!isset($data[$part])) {
                return null;
            }
            $data = $data[$part];
        }

        return $data;
    }

    /**
     * Shares data between views.
     *
     * @param string $key The key of the data.
     * @param mixed $value The value of the data.
     * @return self
     */
    public function share(string $key, mixed $value): self
    {
        $this->sharedData[$key] = $value;
        return $this;
    }

    /**
     * Escapes HTML special characters in a string.
     *
     * @param mixed $value The value to escape.
     * @return string The escaped string.
     */
    public function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sets a configuration value.
     *
     * @param string $key The key of the configuration value.
     * @param mixed $value The value of the configuration value.
     */
    public function set(string $key, mixed $value): void
    {
        if (str_contains($key, ':')) {
            [$section, $key] = explode(':', $key, 2);
            $this->config[$section][$key] = $value;
        } elseif (str_contains($key, '.')) {
            [$section, $key] = explode('.', $key, 2);
            $this->config[$section][$key] = $value;
        } else {
            $this->config[$key] = $value;
        }
    }

    /**
     * Gets a configuration value.
     *
     * @param string $key The key of the configuration value.
     * @return mixed The configuration value, or null if not found.
     */
    public function get(string $key): mixed
    {
        if (str_contains($key, ':')) {
            [$index, $subset] = explode(':', $key, 2);
            return $this->app->config[$index][$subset] ?? null;
        }
        return $this->app->config[$key] ?? null;
    }

    /**
     * Retrieve the current session object.
     *
     * @return object The session object.
     */
    public function session(): object
    {
        return $this->session;
    }

    /**
     * Checks if a configuration value exists.
     *
     * @param mixed $offset The key of the configuration value.
     * @return bool True if the value exists, false otherwise.
     */
    public function offsetExists($offset): bool
    {
        if ($offset === 'session') {
            return true;
        }
        return isset($this->app->config[$offset]);
    }

    /**
     * Gets a configuration value.
     *
     * @param mixed $offset The key of the configuration value.
     * @return mixed The configuration value, or null if not found.
     */
    public function offsetGet($offset): mixed
    {
        if ($offset === 'session') {
            return $this->session;
        }
        return $this->app->config[$offset] ?? null;
    }

    /**
     * Sets a configuration value.
     *
     * @param mixed $offset The key of the configuration value.
     * @param mixed $value The value of the configuration value.
     * @throws \RuntimeException If trying to append to the config.
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === 'session') {
            throw new \RuntimeException('Cannot modify session through array access');
        }
        $this->config[$offset] = $value;
    }

    /**
     * Unsets a configuration value.
     *
     * @param mixed $offset The key of the configuration value.
     */
    public function offsetUnset($offset): void
    {
        if ($offset === 'session') {
            throw new \RuntimeException('Cannot unset session');
        }
        unset($this->config[$offset]);
    }
}