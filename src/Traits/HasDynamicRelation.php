<?php

namespace Jawabapp\RemoteConfig\Traits;

/**
 * Trait HasDynamicRelation
 *
 * Allows models to have dynamically added relationships at runtime.
 * This is useful for polymorphic relationships that need to be
 * registered based on configuration.
 */
trait HasDynamicRelation
{
    /**
     * Store the relations
     *
     * @var array
     */
    private static array $dynamic_relations = [];

    /**
     * Add a new dynamic relation
     *
     * @param string $name
     * @param \Closure $closure
     * @return void
     */
    public static function addDynamicRelation(string $name, \Closure $closure): void
    {
        static::$dynamic_relations[$name] = $closure;
    }

    /**
     * Determine if a relation exists in dynamic relationships list
     *
     * @param string $name
     * @return bool
     */
    public static function hasDynamicRelation(string $name): bool
    {
        return array_key_exists($name, static::$dynamic_relations);
    }

    /**
     * If the key exists in relations then return call to relation
     * or else return the call to the parent
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (static::hasDynamicRelation($name)) {
            // check the cache first
            if ($this->relationLoaded($name)) {
                return $this->relations[$name];
            }

            // load the relationship
            return $this->getRelationshipFromMethod($name);
        }

        return parent::__get($name);
    }

    /**
     * If the method exists in relations then return the relation
     * or else return the call to the parent
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (static::hasDynamicRelation($name)) {
            return call_user_func(static::$dynamic_relations[$name], $this);
        }

        return parent::__call($name, $arguments);
    }
}
