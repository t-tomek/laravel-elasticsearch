<?php

namespace T2\ElasticLaravel\Query;

use Illuminate\Database\Query\Processors\Processor as BaseProcessor;

class Processor
{
    /**
     * Process the results of a "select" request.
     *
     * @param  \T2\ElasticLaravel\Query\Builder  $query
     * @param  array  $results
     * @return array
     */
    public function processSelect(Builder $query, $results)
    {
        return array_map(function ($result) {
            return array_merge($result['_source'], ['_id'=> $result['_id']]);
        }, $results['hits']['hits']);
    }

        /**
     * Process the results of a "count" request.
     *
     * @param  \T2\ElasticLaravel\Query\Builder  $query
     * @param  array  $results
     * @return int
     */
    public function processCount(Builder $query, $results)
    {
        return (int) $results['count'];
    }
}
