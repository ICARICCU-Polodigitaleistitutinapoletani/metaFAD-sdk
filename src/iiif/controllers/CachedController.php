<?php
class iiif_controllers_CachedController extends pinax_rest_core_CommandRest
{
    protected function cache()
    {
       $cache = pinax_ObjectFactory::createObject('pinax.cache.CacheFunction', $this);
       return $cache;
    }

    protected function getFromCache($uid, $callback)
    {
        $cache = $this->cache();
        $result = $cache->get(__CLASS__ . $uid, [], function() use ($callback) {
            return $callback();
        });
        return $result;
    }
}
