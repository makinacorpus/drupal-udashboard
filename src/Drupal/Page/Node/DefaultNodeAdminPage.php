<?php

namespace MakinaCorpus\Dashboard\Drupal\Page\Node;

use Drupal\Core\Session\AccountInterface;
use MakinaCorpus\Dashboard\Datasource\DatasourceInterface;
use MakinaCorpus\Dashboard\Datasource\InputDefinition;
use MakinaCorpus\Dashboard\Page\PageBuilder;
use MakinaCorpus\Dashboard\Page\PageDefinitionInterface;
use MakinaCorpus\Dashboard\View\ViewDefinition;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default node admin page implementation, suitable for most use cases
 */
class DefaultNodeAdminPage implements PageDefinitionInterface
{
    private $datasource;
    private $queryFilter;
    private $permission;

    /**
     * Default constructor
     *
     * @param DatasourceInterface $datasource
     * @param string $permission
     * @param mixed[] $queryFilter
     */
    public function __construct(DatasourceInterface $datasource, $permission, array $queryFilter = [])
    {
        $this->datasource = $datasource;
        $this->permission = $permission;
        $this->queryFilter = $queryFilter;
    }

    /**
     * Get datasource
     *
     * @return DatasourceInterface
     */
    final protected function getDatasource()
    {
        return $this->datasource;
    }

    /**
     * Get default query filters
     *
     * @return array
     */
    final protected function getQueryFilters()
    {
        return $this->queryFilter ? $this->queryFilter : [];
    }

    /**
     * {@inheritdoc}
     */
    public function userIsGranted(AccountInterface $account)
    {
        return $account->hasPermission($this->permission);
    }

    /**
     * For implementors, attach to this function to set default filters
     * for your admin screen
     *
     * @param PageBuilder $builder
     */
    protected function prepareDefaultFilters(PageBuilder $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function build(PageBuilder $builder, InputDefinition $inputDefinition, Request $request)
    {
        $viewDefinition = new ViewDefinition([
            'default_display' => 'page',
            'templates' => [
                'grid' => 'module:udashboard:views/Page/page-grid.html.twig',
                'table' => 'module:udashboard:views/Page/page.html.twig',
            ],
        ]);

        $builder
            ->setInputDefinition($inputDefinition)
            ->setViewDefinition($viewDefinition)
            ->setDatasource($this->getDatasource())
        ;

        /*
         * @todo this must happen in createInputDefinition()
         *
        foreach ($this->getQueryFilters() as $name => $value) {
            $builder->addBaseQueryParameter($name, $value);
        }
         */
    }
}
