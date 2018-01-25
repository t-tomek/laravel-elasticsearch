<?php

namespace T2\ElasticLaravel\Query;

use Illuminate\Support\Str;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;

class Grammar
{
    protected $dql = [];
    /**
     * Compile a select query into DQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compielSelect(Builder $query)
    {
        // To compile the query, we'll spin through each component of the query and
        // see if that component exists. If it does we'll just call the compiler
        // function for the component which is responsible for making the SQL.
        return $this->compileSearch($query);
    }

    public function compileSearch(Builder $query)
    {
        $this->dql  = [
            'index' => $query->indices,
            'type'  => $query->types,
            'body'  => [],
        ];

        $this->compileComponents($query);

        // dd($this->dql);

        return $this->dql;
    }

  /**
     * Compile the components necessary for a select clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return array
     */
    protected function compileComponents(Builder $query)
    {
        $this->compileSource($query);
        $this->compileWheres($query);
        $this->compileOrders($query);
        $this->compileFrom($query);
        $this->compileSize($query);
    }

    protected function compileWheres(Builder $query)
    {
        foreach ($query->wheres as $where) {
            $type = $where['type'] == '=' ? 'term' : $where['type'];

            $this->dql['body']['query']['bool'][$where['bool']][] = $this->{'compile'.Str::camel($type)}($where['field'], $where['value']);
        }
    }

    protected function compileTerm($field, $value, $boost = 1.0)
    {
        return [
            'term' => [ $field => compact('value', 'boost') ]
        ];
    }

    protected function compileTerms($field, $values)
    {
        return [
            'terms' => [ $field => $values ]
        ];
    }

    protected function compileRange($field, $range, $boost = 1.0, $format = null, $timezone = null)
    {
        $result = [];
        $result['range'][$field] = $range;
        $result['range'][$field]['boost'] = $boost;

        if ($format) {
            $result['range'][$field]['format'] = $format;
        }
        if ($timezone) {
            $result['range'][$field]['time_zone'] = $timezone;
        }

        return $result;
    }

    protected function compileQueryString($field, $query)
    {
        return [
            'query_string' => [
                'default_field' => $field,
                'query'         => $query
            ]
        ];
    }

    protected function compileRegex($field, $query)
    {
        return [
            'regexp' => [
                $field => [
                    'value' => $query
                ]
            ]
        ];
    }

    protected function compileSource(Builder $query)
    {
        if ($query->columns) {
            $this->dql['body']['_source']['includes'] = $query->columns;
        }
        if ($query->columns_exclude) {
            $this->dql['body']['_source']['excludes'] = $query->columns_exclude;
        }
    }

    protected function compileOrders(Builder $query)
    {
        if ($query->orders) {
            $this->dql['body']['sort'] = $query->orders;
        };
    }

    protected function compileSize(Builder $query)
    {
        if (!is_null($query->limit)) {
            $this->dql['body']['size'] = $query->limit;
        };
    }

    protected function compileFrom(Builder $query)
    {
        if (!is_null($query->offset)) {
            $this->dql['body']['from'] = $query->offset;
        };
    }
}
