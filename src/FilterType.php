<?php

namespace T2\ElasticLaravel;

class FilterType
{
    const TERM  = 'term';
    const RANGE = 'range';
    const BETWEEN = 'between';
    const QUERY_STRING = 'query_string';

    const GTE = '>=';
    const LTE = '<=';

    const BOOL_SHOULD   = 'should';
    const BOOL_MUST     = 'must';
    const BOOL_MUST_NOT = 'must_not';
    const BOOL_FILTER   = 'filter';
}
