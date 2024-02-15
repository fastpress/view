<?php declare(strict_types=1);

namespace Fastpress\Presentation;

/**
 * Templating object
 *
 * @category   fastpress
 *
 * @author     https://github.com/samayo
 */
class View
{
    private $app;
    private $data;
    private $block = [];
    private $layout = 'layout.html';

    /**
     * View constructor.
     *
     * @param array $conf
     * @param mixed $app
     *
     * @throws \InvalidArgumentException
     */
    public function __construct( $app )
    {
        if (empty($conf)) {
            // throw new \InvalidArgumentException(
            //     'template class requires at least one runtime configuration'
            // );
        }

        // ($app);

        $this->app = $app;
        // $this->conf = $conf;
    }

    public function set($option, $value = null) {
        if (strpos($option, ':')) {
            [$index, $subset] = explode(':', $option);
            $this->conf[$index] = [$subset => $value] + ($this->conf[$index] ?? []);
        } else {
            $this->conf[$option] = $value;
        }

        return $this;
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
        $app = $this->app;
        $this->layout = $this->conf['template']['layout'] . $layout;
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
}