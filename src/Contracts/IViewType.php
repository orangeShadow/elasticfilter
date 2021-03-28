<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Contracts;

/**
 * Class ViewType
 * @package OrangeShadow\ElasticFilter
 */
interface IViewType
{
    const CHECKBOX = 'checkbox';
    const RADIO = 'radio';
    const RANGE = 'range';
}
