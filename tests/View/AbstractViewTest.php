<?php

namespace MakinaCorpus\Dashboard\Tests\View;

use MakinaCorpus\Dashboard\Tests\Mock\ContainerAwareTestTrait;
use MakinaCorpus\Dashboard\Tests\Mock\DummyView;
use MakinaCorpus\Dashboard\View\ViewDefinition;
use MakinaCorpus\Dashboard\Tests\Mock\IntItem;
use MakinaCorpus\Dashboard\View\PropertyView;

/**
 * Tests the views
 */
class AbstractViewTest extends \PHPUnit_Framework_TestCase
{
    use ContainerAwareTestTrait;

    /**
     * Tests property normalization with the property info component
     */
    public function testPropertyNormalization()
    {
        $container = $this->createContainerWithPageDefinitions();
        $container->compile();

        $view = new DummyView();
        $view->setContainer($container);

        // If there is no properties, all from the original class should
        // be found with default options instead
        $viewDefinition = new ViewDefinition([
            'view_type' => $view,
        ]);
        $properties = $view->normalizePropertiesPassthrought($viewDefinition, IntItem::class);
        $this->assertCount(8, $properties);

        // If a list of properties is defined, the algorithm should not
        // attempt to use the property info component for retrieving the
        // property list
        $viewDefinition = new ViewDefinition([
            'properties' => [
                'foo' => [
                    'thousand_separator' => 'YOUPLA',
                    'label' => "The Foo property",
                ],
                'id' => true,
                'baz' => false,
                'test' => [
                    'callback' => function () {
                        return 'test';
                    }
                ],
            ],
            'view_type' => $view,
        ]);

        $properties = $view->normalizePropertiesPassthrought($viewDefinition, IntItem::class);
        $this->assertCount(3, $properties);
        reset($properties);

        // Order is the same, we have all properties we defined
        // 'foo' is the first
        /** @var \MakinaCorpus\Dashboard\View\PropertyView $property */
        $property = current($properties);
        $this->assertInstanceOf(PropertyView::class, $property);
        $this->assertSame('foo', $property->getName());
        $this->assertSame('YOUPLA', $property->getOptions()['thousand_separator']);
        // Label is found from the options array
        $this->assertSame("The Foo property", $property->getLabel());
        $this->assertFalse($property->isVirtual());
        $this->assertTrue($viewDefinition->isPropertyDisplayed('foo'));

        // Then 'id', which exists on the class
        $property = next($properties);
        $this->assertSame('id', $property->getName());
        $this->assertFalse($property->isVirtual());
        $this->assertTrue($viewDefinition->isPropertyDisplayed('id'));
        // Label is found from the property info component (notice the caps)
        $this->assertSame("Id", $property->getLabel());

        // Baz is not there
        $this->assertFalse($viewDefinition->isPropertyDisplayed('baz'));

        // Then 'test'
        $property = next($properties);
        $this->assertSame('test', $property->getName());
        $this->assertFalse($property->isVirtual());
        $this->assertTrue($viewDefinition->isPropertyDisplayed('test'));
        $this->assertTrue(is_callable($property->getOptions()['callback']));
        // Label is just the property name
        $this->assertSame("test", $property->getLabel());
    }

    /**
     * Tests property normalization without the property info component
     */
    public function testPropertyNormalizationWithoutContainer()
    {
        $view = new DummyView();

        // No property info, no properties.
        $viewDefinition = new ViewDefinition([
            'view_type' => $view,
        ]);
        $properties = $view->normalizePropertiesPassthrought($viewDefinition, IntItem::class);
        $this->assertCount(0, $properties);

        // If a list of properties is defined, the algorithm should not
        // attempt to use the property info component for retrieving the
        // property list
        $viewDefinition = new ViewDefinition([
            'properties' => [
                'foo' => [
                    'thousand_separator' => 'YOUPLA',
                    'label' => "The Foo property",
                ],
                'id' => true,
                'baz' => false,
                'test' => [
                    'callback' => function () {
                        return 'test';
                    }
                ],
            ],
            'view_type' => $view,
        ]);

        $properties = $view->normalizePropertiesPassthrought($viewDefinition, IntItem::class);
        reset($properties);

        // Trust the user, display everything
        foreach ($properties as $property) {
            $name = $property->getName();
            if ('foo' === $name) {
                $this->assertSame("The Foo property", $property->getLabel());
            } else {
                $this->assertSame($property->getName(), $property->getLabel());
            }
            $this->assertFalse($property->isVirtual());
        }
    }
}