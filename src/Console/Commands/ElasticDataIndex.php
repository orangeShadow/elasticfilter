<?php

namespace OrangeShadow\ElasticFilter\Console\Commands;

use Illuminate\Console\Command;
use OrangeShadow\ElasticFilter\Exceptions\ElasticFilterException;
use OrangeShadow\ElasticFilter\IndexConfig;
use OrangeShadow\ElasticFilter\Managers\ElasticManager;
use OrangeShadow\ElasticFilter\MappingType;

/**
 * Class ElasticDataIndex
 * @package OrageSahdow\ElasticFilter\Console\Commands
 */
class ElasticDataIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:filter-index {configPath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index data for filter';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $configPath = $this->argument('configPath');
        try {
            $indexConfig = new IndexConfig($configPath);
            $elasticManager = new ElasticManager($indexConfig);
            $nexIndex = $elasticManager->createIndex();
            $elasticManager->setAlias($nexIndex);
            $mapping = $indexConfig->getMapping();
            foreach ($indexConfig->getClassName()::getDataForElastic() as $item) {
                foreach ($item as $code => &$value) {
                    if (isset($mapping[$code]) && $mapping[ $code ] === MappingType::FILTERED_NESTED) {
                        $value['computed'] = implode('||', [$value['value'], $value['slug']]);
                    }
                }

                $elasticManager->addElement($item[ $indexConfig->getPrimaryKey() ], $item);
            }

        } catch (ElasticFilterException $e) {
            $this->error($e->getMessage() . ', file:' . $e->getFile() . ', line:' . $e->getLine());
            var_dump($e->getPrevious()->getMessage());
        }


        return 1;
    }
}
