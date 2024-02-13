<?php declare(strict_types=1);

use Fastpress\View\View;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the View class.
 */
class ViewTest extends TestCase
{
    private $view;
    private $app;

    protected function setUp(): void
    {
        $this->app = ['template' => ['views' => '/path/to/views/', 'layout' => '/path/to/layout/']];
        $this->view = new View($this->app);
    }

    public function testSetAndGet(): void
    {
        $this->view->set('option', 'value');
        $this->assertEquals('value', $this->view->get('option'));
    }

    public function testRender(): void
    {
        $testView = 'testView.php';
        file_put_contents($this->app['template']['views'] . $testView, '<p>Hello, World!</p>');
        ob_start();
        $this->view->render($testView);
        $content = ob_get_clean();
        $this->assertEquals('<p>Hello, World!</p>', $content);
        unlink($this->app['template']['views'] . $testView);
    }

    public function testRenderThrowsExceptionForNonexistentView(): void
    {
        $this->expectException(\Exception::class);
        $this->view->render('nonexistentView.php');
    }

    public function testExtend(): void
    {
        $layout = 'myLayout';
        $this->view->extend($layout);
        $this->assertStringContainsString($layout . '.html', $this->view->getLayout());
    }

    public function testContent(): void
    {
        $blockName = 'testBlock';
        $content = 'Test Content';
        $this->view->startBlock($blockName);
        echo $content;
        $this->view->endBlock($blockName);
        ob_start();
        $this->view->content($blockName);
        $output = ob_get_clean();
        $this->assertEquals($content, $output);
    }

    public function testLayout(): void
    {
        $layout = 'default';
        $this->view->layout($layout);
        $this->assertStringContainsString($layout, $this->view->getLayout());
    }

    public function testBlockAndEndBlock(): void
    {
        $blockName = 'testBlock';
        $this->view->startBlock($blockName);
        $this->assertEquals($blockName, $this->view->getCurrentBlock());
        $this->view->endBlock($blockName);
        $this->assertArrayHasKey($blockName, $this->view->getBlocks());
    }

    public function testEndBlockThrowsExceptionForUnknownBlock(): void
    {
        $this->expectException(\Exception::class);
        $this->view->endBlock('unknownBlock');
    }
}
