<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Url;


class UrlParserResult
{
    protected $prefix='';
    protected $queryParams=[];

    /**
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param mixed $prefix
     */
    public function setPrefix($prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param mixed $queryParams
     */
    public function setQueryParams($queryParams): self
    {
        $this->queryParams = $queryParams;

        return $this;
    }


}
