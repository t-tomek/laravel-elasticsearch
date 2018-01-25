<?php

namespace T2\ElasticLaravel\Query;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Str;

class Builder
{
    /**
     * The ElasticSeatch client instance.
     *
     * @var Client
     */
    public $client;

    /**
     * The query grammar instance.
     *
     * @var Gramar
     */
    public $grammar;

    /**
     * The query post processor instance.
     *
     * @var Processor
     */
    public $processor;

    /**
     * The columns which should be returned in _source.
     *
     * @var array
     */
    public $columns = ['*'];


    /**
     * The columns which should be ommited in _source.
     *
     * @var array
     */
    public $columns_exclude = [];

    /**
     * Indices which should be searched by query.
     *
     * @var array
     */
    public $indices = [];

    /**
     * Types which should be searched by query.
     *
     * @var array
     */
    public $types = [];

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The orderings for the query.
     *
     * @var array
     */
    public $orders = [];

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public $limit;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    public $offset;

    /**
     * All of the available clause operators.
     *
     * @var array
     */
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'term', 'terms', 'range', 'query_string',
    ];

    /**
     * Create a new query builder instance.
     *
     * @param Client|null
     * @param Grammar|null
     * @param Processor|null
     */
    public function __construct(Client $client = null, Grammar $grammar = null, Processor $processor = null)
    {
        $this->client = $client ?: \Elasticsearch\ClientBuilder::create()->build();
        $this->grammar = $grammar ?: new Grammar();
        $this->processor = $processor ?: new Processor();
    }

    /**
     * Set the columns to be selected.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    // TODO:
    // public function selectRaw($expression)

    /**
     * Add a new select column to the query.
     *
     * @param  array|mixed  $column
     * @return $this
     */
    public function addSelect($column)
    {
        $column = is_array($column) ? $column : func_get_args();

        $this->columns = array_merge((array) $this->columns, $column);

        return $this;
    }

    /**
     * Set the indices and/or types which the query is targeting.
     *
     * @param  string|string[]      $indices
     * @param  null|string|string[] $types
     * @return $this
     */
    public function from($indices, $types = null)
    {
        $this->indices  = (array) $indices;
        $this->types    = (array) $types;

        return $this;
    }

    /**
     * @param  string       $column
     * @param  null|string  $operator
     * @param  null|mixed   $value
     * @param  null|string  $boolean
     * @param  array        $params
     * @return $this
     */
    public function where(string $column, $operator = null, $value = null, $boolean = null, $params = [])
    {
        $params1 = func_get_args();

        if (func_num_args() == 2) {
            $type = 'term';
            $value = $operator;
            $boolean = $boolean ?? 'filter';
        } else {
            switch ($operator) {
                case '=':
                    $type = 'term';
                    $boolean = 'filter';
                    break;
                case '!=':
                case '<>':
                    $type = 'term';
                    $boolean = 'must_not';
                    break;
                case '>=':
                    $type = 'range';
                    $boolean = 'filter';
                    $value = ['gte' => $value];
                    break;
                case '>':
                    $type = 'range';
                    $boolean = 'filter';
                    $value = ['gt' => $value];
                    break;
                case '<=':
                    $type = 'range';
                    $boolean = 'filter';
                    $value = ['lte' => $value];
                    break;
                case '<':
                    $type = 'range';
                    $boolean = 'filter';
                    $value = ['lt' => $value];
                    break;
                default:
                    $type = $operator;
                    $boolean = $boolean ?? 'filter';
                    break;
            }
        }


        $this->wheres[] = [
            'field' => $column,
            'type'  => $type,
            'value' => $value,
            'bool'  => $boolean,
            'params' => $params,
        ];

        return $this;
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param  string  $dsl
     * @param  string  $boolean
     * @return $this
     */
    public function whereRaw($dsl, $boolean = 'filter')
    {
        $this->wheres[] = ['type' => 'raw', 'dsl' => $dsl, 'bool' => $boolean];

        return $this;
    }

   /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    public function whereIn($column, $values, $params = [], $not = false)
    {
        $this->where($column, 'terms', $values, $not ? 'must_not' : 'filter', $params);

        return $this;
    }

   /**
     * Add a "where not in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @return \T2\ElasticLaravel\Query\Builder|static
     */
    public function whereNotIn($column, $values, $params = [])
    {
        return $this->whereIn($column, $values, $params, true);
    }

    /**
     * @param  string   $column
     * @param  mixed    $from
     * @param  mixed    $to
     * @param  array    $params
     * @param  bool     $not
     * @return $this
     */
    public function whereBetween($column, $from, $to, $params = [], $not = false)
    {
        $range = [
            'gte' => $from,
            'lte' => $to,
        ];

        $this->where($column, 'range', $range, $not ? 'must_not' : 'filter', $params);

        return $this;
    }

    /**
     * @param  string   $column
     * @param  mixed    $from
     * @param  mixed    $to
     * @param  array    $params
     * @return \T2\ElasticLaravel\Query\Builder|static
     */
    public function whereNotBetween($column, $from, $to, $params = [])
    {
        return $this->whereBetween($column, $from, $to, $params, true);
    }

    public function whereQueryString($column, $query, $params = [])
    {
        $this->where($column, 'query_string', $query, 'must', $params);

        return $this;
    }

    public function whereRegex($column, $query, $params = [])
    {
        $this->where($column, 'regex', $query, 'filter', $params);

        return $this;
    }

    /**
     * @param  string   $column
     * @param  string   $order
     * @param  string|null  $mode
     * @return [type]
     */
    public function orderBy($column, $order = 'asc', $mode = null)
    {
        $this->orders[$column]['order'] = $order;

        if (!is_null($mode)) {
            $this->orders[$column]['mode'] = $mode;
        }

        return $this;
    }

    /**
     * @param  string   $column
     * @param  string|null  $mode
     * @return \T2\ElasticLaravel\Query\Builder|static
     */
    public function orderByDesc($column, $mode = null)
    {
        return $this->orderBy($column, 'desc', $mode);
    }


    /**
     * Alias to set the "offset" value of the query.
     *
     * @param  int  $value
     * @return \T2\ElasticLaravel\Query\Builder|static
     */
    public function skip($value)
    {
        return $this->offset($value);
    }

    /**
     * Set the "offset" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function offset($value)
    {
        $this->offset = max(0, $value);

        return $this;
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param   int   $value
     * @return  \T2\ElasticLaravel\Query\Builder|static
     */
    public function take($value)
    {
        return $this->limit($value);
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function limit($value)
    {
        if ($value >= 0) {
            $this->limit = $value;
        }

        return $this;
    }

     /**
     * Set the limit and offset for a given page.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @return \T2\ElasticLaravel\Query\Builder|static
     */
    public function forPage($page, $perPage = 15)
    {
        return $this->skip(($page - 1) * $perPage)->take($perPage);
    }

    /**
     * Get the DSL representation of the query.
     *
     * @return string
     */
    public function toDsl()
    {
        return $this->grammar->compileSelect($this);
    }

   /**
     * Execute a query for a single record by ID.
     *
     * @param  int    $id
     * @param  array  $columns
     * @return mixed|static
     */
    public function find($id, $columns = ['*'])
    {
        return $this->where('_id', '=', $id)->first($columns);
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param  string  $column
     * @return mixed
     */
    public function value($column)
    {
        $result = (array) $this->first([$column]);

        return count($result) > 0 ? reset($result) : null;
    }

    /**
     * Execute the query as a "search" request.
     *
     * @param  array  $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        $results = $this->processor->processSelect($this, $this->runSearch());

        $this->columns = $original;

        return collect($results);
    }

    /**
     * Run the query as a "search" request against the connection.
     *
     * @return array
     */
    protected function runSearch()
    {
        return $this->client->search($this->toDsl());
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param  string  $column
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection
     */
    public function pluck($column, $key = null)
    {
        $results = $this->get(is_null($key) ? [$column] : [$column, $key]);

        return $results->pluck($column, $key);
    }

    /**
     * Concatenate values of a given column as a string.
     *
     * @param  string  $column
     * @param  string  $glue
     * @return string
     */
    public function implode($column, $glue = '')
    {
        return $this->pluck($column)->implode($glue);
    }

    /**
     * Execute the query as a "count" request.
     *
     * @return int
     */
    public function count()
    {
        $results = $this->processor->processCount($this, $this->runCount());

        return $results;
    }

    /**
     * Run the query as a "count" request against the connection.
     *
     * @return array
     */
    protected function runCount()
    {
        return $this->client->count(
            $this->cloneWithout(['offset', 'limit', 'orders', 'columns', 'columns_exclude'])->toDsl()
        );
    }

    /**
     * Get a new instance of the query builder.
     *
     * @return \T2\ElasticLaravel\Query\Builder
     */
    public function newQuery()
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Get the database connection instance.
     *
     * @return \Elasticsearch\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the database query processor instance.
     *
     * @return Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Get the query grammar instance.
     *
     * @return Grammar
     */
    public function getGrammar()
    {
        return $this->grammar;
    }

   /**
     * Clone the query without the given properties.
     *
     * @param  array  $except
     * @return static
     */
    public function cloneWithout(array $except)
    {
        return tap(clone $this, function ($clone) use ($except) {
            foreach ($except as $property) {
                $clone->{$property} = null;
            }
        });
    }
}
