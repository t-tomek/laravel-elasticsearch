<?php

namespace T2\ElasticLaravel;

use Closure;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Database\Connection as BaseConnection;
use T2\ElasticLaravel\Query\Builder as QueryBuilder;

class Connection extends BaseConnection
{
    /**
     * The Elasticsearch connection handler.
     *
     * @var \Elasticsearch\Client
     */
    protected $connection;

    /**
     * Create a new database connection instance.
     *
     * @param  array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        unset($config['name']);

        // Create the connection
        $this->connection = $this->createConnection($config);
    }


    /**
     * Create a new ElasticSearch connection.
     *
     * @param  array $config
     * @param  array $options
     * @return \Elasticsearch\Client
     */
    protected function createConnection(array $config)
    {
        return ClientBuilder::fromConfig($config);
    }

    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        unset($this->connection);
    }


    public function search($query)
    {
        return $this->connection->search($query);
    }

    public function count($query)
    {
        return $this->connection->count($query);
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param  string  $table
     * @return \Illuminate\Database\Query\Builder
     */
    public function index($index, $type = null)
    {
        return $this->query()->from($index, $typw);
    }

    /**
     * Alias for index method.
     *
     * @param  string  $table
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($index, $type = null)
    {
        return $this->index($index, $type);
    }

    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder($this);
    }

        /**
     * Get a new raw query expression.
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value)
    {
        throw new \Exception('Not supported');
    }

    /**
     * Run a select statement and return a single result.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return mixed
     */
    public function selectOne($query, $bindings = [])
    {
        throw new \Exception('Not supported');
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return array
     */
    public function select($query, $bindings = [])
    {
        throw new \Exception('Not supported');
    }

    /**
     * Run an insert statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        throw new \Exception('Not supported');
    }

    /**
     * Run an update statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
        throw new \Exception('Not supported');
    }

    /**
     * Run a delete statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        throw new \Exception('Not supported');
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        throw new \Exception('Not supported');
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        throw new \Exception('Not supported');
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param  string  $query
     * @return bool
     */
    public function unprepared($query)
    {
        throw new \Exception('Not supported');
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        throw new \Exception('Not supported');
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure  $callback
     * @param  int  $attempts
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        throw new \Exception('Not supported');
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        throw new \Exception('Not supported');
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        throw new \Exception('Not supported');
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        throw new \Exception('Not supported');
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        throw new \Exception('Not supported');
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param  \Closure  $callback
     * @return array
     */
    public function pretend(Closure $callback)
    {
        throw new \Exception('Not supported');
    }
}
