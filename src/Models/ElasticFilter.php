<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Models;


use Illuminate\Database\Eloquent\Model;
use OrangeShadow\ElasticFilter\Contracts\IViewType;
use OrangeShadow\ElasticFilter\Exceptions\TypeFilterException;

class ElasticFilter extends Model implements IViewType
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'url',
        'title',
        'type',
        'index',
        'slug',
        'url_slug',
        'sort',
        'step',
        'unit',
        'hint'
    ];

    /**
     * @param string $type
     * @return $this
     * @throws TypeFilterException
     */
    public function setTypeAttribute(string $type): self
    {
        $ref = new \ReflectionClass(IViewType::class);
        $typeList = $ref->getConstants();

        if (in_array($type, $typeList, true)) {
            throw new TypeFilterException();
        }

        $this->type = $type;

        return $this;
    }

}
