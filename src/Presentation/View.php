<?php declare(strict_types=1);

namespace Fastpress\Presentation;

/**
 * Templating object
 *
 * @category   fastpress
 *
 * @author     https://github.com/samayo
 */
class View implements \ArrayAccess
{
    private $app;
    private $data;
    private $block = [];
    private $layout = 'layout.html';


    /**
     * View constructor.
     *
     * @param mixed $app
     *
     * @throws \InvalidArgumentException
     */
    public function __construct( &$app )
    {
        if (empty($conf)) {
            // throw new \InvalidArgumentException(
            //     'template class requires at least one runtime configuration'
            // );
            // @todo check for template variables from $app
            
        }

        // ($app);

        $this->app = $app;
        // $this->conf = $conf;
    }

    public function set($option, $value = null) {
        if (strpos($option, ':')) {
            [$index, $subset] = explode(':', $option);
            $this->app->config[$index] = [$subset => $value] + ($this->app->config[$index] ?? []);
        } else {
            $this->app->config[$option] = $value;
        }

    }

    public function get($option) {
        // get props from $app if user has : then explode and get the subset
        $parts = explode(':', $option);
        if (count($parts) > 1) {
            return $this->app->config[$parts[0]][$parts[1]];
        }
        
        return $this->app->config[$option] ?? null;
    }
    
    

    /**
     * Render a view.
     *
     * @param string $view
     * @param array  $vars
     *
     * @return View
     *
     * @throws \Exception
     */
    public function render(string $view, array $vars = []): self
    {
        // $conf = $this->conf; 
        $app = $this->app;
            extract($vars, EXTR_SKIP);
            if (file_exists($view = $this->app['template']['views'] . $view)) {
                require $view;
            } else {
                throw new \Exception(sprintf(
                    "%s template does not exist in %s ",
                    $view,
                    $this->app['template']['views']
                ));
            }
        return $this;
    }

    /**
     * Extend the layout.
     *
     * @param string $layout
     *
     * @return View
     */
    public function extend(string $layout): self
    {
        $this->layout = $this->app['template']['layout'] . $layout . '.html';
        return $this;
    }

    /**
     * Get the content of a named block.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function content(string $name)
    {
        if (array_key_exists($name, $this->block)) {
            echo $this->data;
        }
    }

    /**
     * Set the layout and return it.
     *
     * @param string|null $layout
     * @param array       $vars
     *
     * @return string
     * @todo delete
     */
    public function layout(string $layout = null, array $vars = []): string
    {
        $layout = $layout ? $layout : $this->layout;
        // $app = $this->app; Todo remove
        $this->layout = $this->app['template']['layout'] . $layout;
        return $this->layout;
    }

    /**
     * Start a named block.
     *
     * @param string $name
     *
     * @return void
     */
    public function block(string $name): View
    {

        $this->block[$name] = $name;
        ob_start();
        return $this;
    }

    /**
     * End a named block and include it in the layout.
     *
     * @param string $name
     *
     * @return void
     *
     * @throws \Exception
     */
    public function endblock(string $name): void
    {
        
        if (!array_key_exists($name, $this->block)) {
            throw new \Exception($name .' is an unknown block');
        }
        $app = $this->app;
        // $conf = $this->conf;

        $this->data = ob_get_contents();
        ob_end_clean();


        require $this->layout;
    }

    /**
     * Checks if an offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->app[$offset]);
    }

    /**
     * Gets the value at the specified offset
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->app[$offset] ?? null;
    }

    /**
     * Sets the value at the specified offset
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->app[] = $value;
        } else {
            $this->app[$offset] = $value;
        }
    }

    /**
     * Unsets the value at the specified offset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->app[$offset]);
    }
}