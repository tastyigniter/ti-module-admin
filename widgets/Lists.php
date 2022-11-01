<?php

namespace Admin\Widgets;

use Admin\Classes\BaseWidget;
use Admin\Classes\ListColumn;
use Admin\Classes\ToolbarButton;
use Admin\Classes\Widgets;
use Admin\Facades\AdminAuth;
use Admin\Traits\LocationAwareWidget;
use Carbon\Carbon;
use Exception;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Html\HtmlFacade as Html;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Lists extends BaseWidget
{
    use LocationAwareWidget;

    /**
     * @var array List action configuration.
     */
    public $bulkActions;

    /**
     * @var array List column configuration.
     */
    public $columns;

    /**
     * @var Model List model object.
     */
    public $model;

    /**
     * @var string Message to display when there are no records in the list.
     */
    public $emptyMessage = 'lang:admin::lang.text_empty';

    /**
     * @var int Maximum rows to display for each page.
     */
    public $pageLimit;

    /**
     * @var bool Display a checkbox next to each record row.
     */
    public $showCheckboxes = true;

    /**
     * @var bool Display the list set up used for column visibility and ordering.
     */
    public $showSetup = true;

    /**
     * @var bool|string Display pagination when limiting records per page.
     */
    public $showPagination = 'auto';

    /**
     * @var bool Display page numbers with pagination, disable to improve performance.
     */
    public $showPageNumbers = true;

    /**
     * @var bool Display a drag handle next to each record row.
     */
    public $showDragHandle = false;

    /**
     * @var bool Shows the sorting options for each column.
     */
    public $showSorting = true;

    /**
     * @var mixed A default sort column to look for.
     */
    public $defaultSort;

    protected $defaultAlias = 'list';

    /**
     * @var array Collection of all list columns used in this list.
     * @see ListColumn
     */
    protected $allColumns;

    /**
     * @var array Override default columns with supplied key names.
     */
    protected $columnOverride;

    /**
     * @var array Columns to display and their order.
     */
    protected $visibleColumns;

    /**
     * @var array Model data collection.
     */
    protected $records;

    /**
     * @var int Current page number.
     */
    protected $currentPageNumber;

    /**
     * @var string Filter the records by a search term.
     */
    protected $searchTerm;

    /**
     * @var string If searching the records, specifies a policy to use.
     * - all: result must contain all words
     * - any: result can contain any word
     * - exact: result must contain the exact phrase
     */
    protected $searchMode;

    /**
     * @var string Use a custom scope method for performing searches.
     */
    protected $searchScope;

    /**
     * @var array Collection of functions to apply to each list query.
     */
    protected $filterCallbacks = [];

    /**
     * @var array All sortable columns.
     */
    protected $sortableColumns;

    /**
     * @var string Sets the list sorting column.
     */
    protected $sortColumn;

    /**
     * @var string Sets the list sorting direction (asc, desc)
     */
    protected $sortDirection;

    protected $allBulkActions;

    protected $availableBulkActions;

    protected $bulkActionWidgets = [];

    public function initialize()
    {
        $this->fillFromConfig([
            'bulkActions',
            'columns',
            'model',
            'emptyMessage',
            'pageLimit',
            'showSetup',
            'showPagination',
            'showDragHandle',
            'showCheckboxes',
            'showSorting',
            'defaultSort',
        ]);

        $this->pageLimit = $this->getSession('page_limit',
            $this->pageLimit ?? 20
        );

        if ($this->showPagination == 'auto') {
            $this->showPagination = $this->pageLimit && $this->pageLimit > 0;
        }

        $this->validateModel();
    }

    public function loadAssets()
    {
        $this->addJs('../../../formwidgets/repeater/assets/vendor/sortablejs/Sortable.min.js', 'sortable-js');
        $this->addJs('../../../formwidgets/repeater/assets/vendor/sortablejs/jquery-sortable.js', 'jquery-sortable-js');
        $this->addJs('js/lists.js', 'lists-js');
    }

    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('lists/list');
    }

    public function prepareVars()
    {
        $this->vars['listId'] = $this->getId();
        $this->vars['bulkActions'] = $this->getAvailableBulkActions();
        $this->vars['columns'] = $this->getVisibleColumns();
        $this->vars['columnTotal'] = $this->getTotalColumns();
        $this->vars['records'] = $this->getRecords();
        $this->vars['emptyMessage'] = lang($this->emptyMessage);
        $this->vars['showCheckboxes'] = $this->showCheckboxes;
        $this->vars['showDragHandle'] = $this->showDragHandle;
        $this->vars['showSetup'] = $this->showSetup;
        $this->vars['showFilter'] = count($this->filterCallbacks);
        $this->vars['showPagination'] = $this->showPagination;
        $this->vars['showPageNumbers'] = $this->showPageNumbers;
        $this->vars['showSorting'] = $this->showSorting;
        $this->vars['sortColumn'] = $this->getSortColumn();
        $this->vars['sortDirection'] = $this->sortDirection;
    }

    /**
     * Event handler for refreshing the list.
     */
    public function onRefresh()
    {
        $this->prepareVars();

        return $this->getController()->refresh();
    }

    /**
     * Event handler for switching the page number.
     */
    public function onPaginate()
    {
        $this->currentPageNumber = input('page');

        return $this->onRefresh();
    }

    protected function validateModel()
    {
        if (!$this->model || !$this->model instanceof \Illuminate\Database\Eloquent\Model) {
            throw new Exception(sprintf(lang('admin::lang.list.missing_model'), get_class($this->controller)));
        }

        return $this->model;
    }

    /**
     * Replaces the @ symbol with a table name in a model
     *
     * @param string $sql
     * @param string $table
     *
     * @return string
     */
    protected function parseTableName($sql, $table)
    {
        return str_replace('@', DB::getTablePrefix().$table.'.', $sql);
    }

    /**
     * Applies any filters to the model.
     */
    public function prepareModel()
    {
        $query = $this->model->newQuery();
        $primaryTable = $this->model->getTable();
        $selects = [$primaryTable.'.*'];
        $joins = [];
        $withs = [];

        // Extensibility
        $this->fireSystemEvent('admin.list.extendQueryBefore', [$query]);

        // Prepare searchable column names
        $primarySearchable = [];
        $relationSearchable = [];

        if (!empty($this->searchTerm) && ($searchableColumns = $this->getSearchableColumns())) {
            foreach ($searchableColumns as $column) {
                // Relation
                if ($this->isColumnRelated($column)) {
                    $table = DB::getTablePrefix().$this->model->makeRelation($column->relation)->getTable();
                    $columnName = isset($column->sqlSelect)
                        ? DB::raw($this->parseTableName($column->sqlSelect, $table))
                        : $table.'.'.$column->valueFrom;

                    $relationSearchable[$column->relation][] = $columnName;
                } // Primary
                else {
                    $columnName = isset($column->sqlSelect)
                        ? DB::raw($this->parseTableName($column->sqlSelect, $primaryTable))
                        : DB::getTablePrefix().$primaryTable.'.'.$column->columnName;

                    $primarySearchable[] = $columnName;
                }
            }
        }

        // Prepare related eager loads (withs) and custom selects (joins)
        foreach ($this->getVisibleColumns() as $column) {
            if (!$this->isColumnRelated($column) || (!isset($column->sqlSelect) && !isset($column->valueFrom)))
                continue;

            $withs[] = $column->relation;

            $joins[] = $column->relation;
        }

        // Add eager loads to the query
        if ($withs)
            $query->with(array_unique($withs));

        // Apply search term
        $query->where(function ($innerQuery) use ($primarySearchable, $relationSearchable, $joins) {
            // Search primary columns
            if (count($primarySearchable) > 0) {
                $this->applySearchToQuery($innerQuery, $primarySearchable, 'or');
            }

            // Search relation columns
            if ($joins) {
                foreach (array_unique($joins) as $join) {
                    // Apply a supplied search term for relation columns and
                    // constrain the query only if there is something to search for
                    $columnsToSearch = array_get($relationSearchable, $join, []);
                    if (count($columnsToSearch) > 0) {
                        $innerQuery->orWhereHas($join, function ($_query) use ($columnsToSearch) {
                            $this->applySearchToQuery($_query, $columnsToSearch);
                        });
                    }
                }
            }
        });

        // Custom select queries
        foreach ($this->getVisibleColumns() as $column) {
            if (!isset($column->sqlSelect)) {
                continue;
            }

            $alias = $query->getQuery()->getGrammar()->wrap($column->columnName);

            // Relation column
            if (isset($column->relation)) {
                $relationType = $this->model->getRelationType($column->relation);
                if ($relationType == 'morphTo') {
                    throw new Exception(sprintf(lang('admin::lang.list.alert_relationship_not_supported'), 'morphTo'));
                }

                $table = $this->model->makeRelation($column->relation)->getTable();
                $sqlSelect = $this->parseTableName($column->sqlSelect, $table);

                // Manipulate a count query for the sub query
                $relationObj = $this->model->{$column->relation}();
                $countQuery = $relationObj->getRelationExistenceCountQuery($relationObj->getRelated()->newQueryWithoutScopes(), $query);

                $joinSql = $this->isColumnRelated($column, true)
                    ? Db::raw('group_concat('.$sqlSelect." separator ', ')")
                    : Db::raw($sqlSelect);

                $joinSql = $countQuery->select($joinSql)->toRawSql();

                $selects[] = Db::raw('('.$joinSql.') as '.$alias);
            } // Primary column
            else {
                $sqlSelect = $this->parseTableName($column->sqlSelect, $primaryTable);
                $selects[] = Db::raw($sqlSelect.' as '.$alias);
            }
        }

        // Apply sorting
        if ($sortColumn = $this->getSortColumn()) {
            if (($column = array_get($this->allColumns, $sortColumn)) && $column->valueFrom) {
                $sortColumn = $column->valueFrom;
            }

            $query->orderBy($sortColumn, $this->sortDirection);
        }

        // Apply filters
        foreach ($this->filterCallbacks as $callback) {
            $callback($query);
        }

        // Add custom selects
        $query->select($selects);

        // Extensibility
        if ($event = $this->fireSystemEvent('admin.list.extendQuery', [$query], true)) {
            return $event;
        }

        return $query;
    }

    /**
     * Returns all the records from the supplied model, after filtering.
     * @return Collection
     */
    protected function getRecords()
    {
        $model = $this->prepareModel();

        if (!$this->currentPageNumber)
            $this->currentPageNumber = input('page');

        if ($this->showPagination) {
            $records = $model->paginate($this->pageLimit, $this->currentPageNumber);
        }
        else {
            $records = $model->get();
        }

        if ($event = $this->fireSystemEvent('admin.list.extendRecords', [&$records])) {
            $records = $event;
        }

        return $this->records = $records;
    }

    /**
     * Get all the registered columns for the instance.
     * @return array
     */
    public function getColumns()
    {
        return $this->allColumns ?: $this->defineListColumns();
    }

    /**
     * Get a specified column object
     *
     * @param string $column
     *
     * @return mixed
     */
    public function getColumn($column)
    {
        return $this->allColumns[$column];
    }

    /**
     * Returns the list columns that are visible by list settings or default
     */
    public function getVisibleColumns()
    {
        $definitions = $this->defineListColumns();
        $columns = [];

        if ($this->columnOverride === null) {
            $this->columnOverride = $this->getSession('visible', null);
        }

        if ($this->columnOverride && is_array($this->columnOverride)) {
            $invalidColumns = array_diff($this->columnOverride, array_keys($definitions));
            if (!count($definitions)) {
                throw new Exception(sprintf(
                    lang('admin::lang.list.missing_column'), implode(',', $invalidColumns)
                ));
            }

            $availableColumns = array_intersect($this->columnOverride, array_keys($definitions));
            foreach ($availableColumns as $columnName) {
                $definitions[$columnName]->invisible = false;
                $columns[$columnName] = $definitions[$columnName];
            }
        }
        else {
            foreach ($definitions as $columnName => $column) {
                if ($column->invisible) continue;

                $columns[$columnName] = $definitions[$columnName];
            }
        }

        return $this->visibleColumns = $columns;
    }

    /**
     * Builds an array of list columns with keys as the column name and values as a ListColumn object.
     */
    protected function defineListColumns()
    {
        if (!isset($this->columns) || !is_array($this->columns) || !count($this->columns)) {
            throw new Exception(sprintf(lang('admin::lang.list.missing_column'), get_class($this->controller)));
        }

        $this->addColumns($this->columns);

        // Extensibility
        $this->fireSystemEvent('admin.list.extendColumns');

        // Use a supplied column order
        if ($columnOrder = $this->getSession('order', null)) {
            $orderedDefinitions = [];
            foreach ($columnOrder as $column) {
                if (isset($this->allColumns[$column])) {
                    $orderedDefinitions[$column] = $this->allColumns[$column];
                }
            }

            $this->allColumns = array_merge($orderedDefinitions, $this->allColumns);
        }

        $this->applyFiltersFromModel();

        return $this->allColumns;
    }

    /**
     * Allow the model to filter columns.
     */
    protected function applyFiltersFromModel()
    {
        if (method_exists($this->model, 'filterColumns')) {
            $this->model->filterColumns((object)$this->allColumns);
        }
    }

    /**
     * Programatically add columns, used internally and for extensibility.
     *
     * @param array $columns Column definitions
     */
    public function addColumns(array $columns)
    {
        foreach ($columns as $columnName => $config) {
            // Check if admin has permissions to show this column
            $permissions = array_get($config, 'permissions');
            if (!empty($permissions) && !AdminAuth::getUser()->hasPermission($permissions, false)) {
                continue;
            }

            // Check that the filter scope matches the active location context
            if ($this->isLocationAware($config)) continue;

            $this->allColumns[$columnName] = $this->makeListColumn($columnName, $config);
        }
    }

    public function removeColumn($columnName)
    {
        if (isset($this->allColumns[$columnName])) {
            unset($this->allColumns[$columnName]);
        }
    }

    /**
     * Creates a list column object from it's name and configuration.
     *
     * @param $name
     * @param array $config
     *
     * @return \Admin\Classes\ListColumn
     */
    public function makeListColumn($name, $config)
    {
        if (is_string($config)) {
            $label = $config;
        }
        elseif (isset($config['label'])) {
            $label = $config['label'];
        }
        else {
            $label = studly_case($name);
        }

        if (starts_with($name, 'pivot[') && strpos($name, ']') !== false) {
            $_name = name_to_array($name);
            $config['relation'] = array_shift($_name);
            $config['valueFrom'] = array_shift($_name);
            $config['searchable'] = false;
        }
        elseif (strpos($name, '[') !== false && strpos($name, ']') !== false) {
            $config['valueFrom'] = $name;
            $config['sortable'] = false;
            $config['searchable'] = false;
        }

        $columnType = isset($config['type']) ? $config['type'] : null;

        $column = new ListColumn($name, $label);
        $column->displayAs($columnType, $config);

        return $column;
    }

    protected function makePagination()
    {
        return new LengthAwarePaginator(
            $this->records,
            $this->totalRecordsCount,
            $this->pageLimit,
            $this->currentPageNumber,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Calculates the total columns used in the list, including checkboxes
     * and other additions.
     */
    protected function getTotalColumns()
    {
        $columns = $this->visibleColumns ?: $this->getVisibleColumns();
        $total = count($columns);
        if ($this->showCheckboxes)
            $total++;

        if ($this->showSetup)
            $total++;

        if ($this->showDragHandle)
            $total++;

        return $total;
    }

    /**
     * Looks up the column header
     *
     * @param $column
     *
     * @return
     */
    public function getHeaderValue($column)
    {
        $value = lang($column->label);

        // Extensibility
        if ($response = $this->fireSystemEvent('admin.list.overrideHeaderValue', [$column, $value], true)) {
            $value = $response;
        }

        return $value;
    }

    /**
     * Looks up the column value
     *
     * @param $record
     * @param $column
     *
     * @return null|string
     */
    public function getColumnValue($record, $column)
    {
        $columnName = $column->columnName;

        // Handle taking value from model attribute.
        $value = $this->getValueFromData($record, $column, $columnName);

        if (method_exists($this, 'eval'.studly_case($column->type).'TypeValue')) {
            $value = $this->{'eval'.studly_case($column->type).'TypeValue'}($record, $column, $value);
        }

        // Apply default value.
        if ($value === '' || $value === null)
            $value = $column->defaults;

        // Extensibility
        if ($response = $this->fireSystemEvent('admin.list.overrideColumnValue', [$record, $column, $value], true)) {
            $value = $response;
        }

        if (is_callable($column->formatter) && ($response = call_user_func_array($column->formatter, [$record, $column, $value])) !== null) {
            $value = $response;
        }

        return $value;
    }

    public function getButtonAttributes($record, $column)
    {
        $result = $column->attributes;

        // Extensibility
        if ($response = $this->fireSystemEvent('admin.list.overrideColumnValue', [$record, $column, $result], true)) {
            $result = $response;
        }

        if (!is_array($result))
            $result = '';

        if (isset($result['title']))
            $result['title'] = e(lang($result['title']));

        $result['class'] = isset($result['class']) ? $result['class'] : null;

        foreach ($result as $key => $value) {
            if ($key == 'href' && !preg_match('#^(\w+:)?//#i', $value)) {
                $result[$key] = $this->controller->pageUrl($value);
            }
            elseif (is_string($value)) {
                $result[$key] = lang($value);
            }
        }

        if (isset($result['url'])) {
            $result['href'] = $result['url'];
            unset($result['url']);
        }

        $data = $record->getOriginal();
        $data += [$record->getKeyName() => $record->getKey()];

        return parse_values($data, Html::attributes($result));
    }

    public function getValueFromData($record, $column, $columnName)
    {
        if ($column->valueFrom && $column->relation) {
            $columnName = $column->relation;

            if (!array_key_exists($columnName, $record->getRelations())) {
                $value = null;
            }
            elseif ($this->isColumnRelated($column, true)) {
                $value = implode(', ', $record->{$columnName}->pluck($column->valueFrom)->all());
            }
            elseif ($this->isColumnRelated($column) || $this->isColumnPivot($column)) {
                $value = $record->{$columnName} ? $record->{$columnName}->{$column->valueFrom} : null;
            }
            else {
                $value = null;
            }
        }
        elseif ($column->valueFrom) {
            $keyParts = name_to_array($column->valueFrom);
            $value = $record;
            foreach ($keyParts as $key) {
                $value = $value->{$key};
            }
        }
        else {
            $value = $record->{$columnName};
        }

        return $value;
    }

    //
    // Value processing
    //

    /**
     * Process as text, escape the value
     */
    protected function evalTextTypeValue($record, $column, $value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Process as partial reference
     */
    protected function evalPartialTypeValue($record, $column, $value)
    {
        $response = $this->makePartial($column->path ?: $column->columnName, [
            'listColumn' => $column,
            'listRecord' => $record,
            'listValue' => $value,
            'column' => $column,
            'record' => $record,
            'value' => $value,
        ]);

        return $response;
    }

    /**
     * Process as partial reference
     */
    protected function evalMoneyTypeValue($record, $column, $value)
    {
        return number_format($value, 2);
    }

    /**
     * Process as boolean control
     */
    protected function evalSwitchTypeValue($record, $column, $value)
    {
        $onText = lang($column->config['onText'] ?? 'admin::lang.text_enabled');
        $offText = lang($column->config['offText'] ?? 'admin::lang.text_disabled');

        return $value ? $onText : $offText;
    }

    /**
     * Process as a datetime value
     */
    protected function evalDatetimeTypeValue($record, $column, $value)
    {
        if ($value === null) {
            return null;
        }

        $dateTime = $this->validateDateTimeValue($value, $column);

        $format = $column->format ?? lang('system::lang.moment.date_time_format');
        $format = parse_date_format($format);

        return $dateTime->isoFormat($format);
    }

    /**
     * Process as a time value
     */
    protected function evalTimeTypeValue($record, $column, $value)
    {
        if ($value === null) {
            return null;
        }

        $dateTime = $this->validateDateTimeValue($value, $column);

        $format = $column->format ?? lang('system::lang.moment.time_format');
        $format = parse_date_format($format);

        return $dateTime->isoFormat($format);
    }

    /**
     * Process as a date value
     */
    protected function evalDateTypeValue($record, $column, $value)
    {
        if ($value === null) {
            return null;
        }

        $dateTime = $this->validateDateTimeValue($value, $column);

        $format = $column->format ?? lang('system::lang.moment.date_format');
        $format = parse_date_format($format);

        return $dateTime->isoFormat($format);
    }

    /**
     * Process as diff for humans (1 min ago)
     */
    protected function evalTimesinceTypeValue($record, $column, $value)
    {
        if ($value === null) {
            return null;
        }

        $dateTime = $this->validateDateTimeValue($value, $column);

        return $dateTime->diffForHumans();
    }

    /**
     * Process as diff for humans (today)
     */
    protected function evalDatesinceTypeValue($record, $column, $value)
    {
        if ($value === null) {
            return null;
        }

        $dateTime = $this->validateDateTimeValue($value, $column);

        return day_elapsed($dateTime, false);
    }

    /**
     * Process as time as current tense (Today at 0:00)
     */
    protected function evalTimetenseTypeValue($record, $column, $value)
    {
        if ($value === null) {
            return null;
        }
        $dateTime = $this->validateDateTimeValue($value, $column);

        return day_elapsed($dateTime);
    }

    /**
     * Process as partial reference
     */
    protected function evalCurrencyTypeValue($record, $column, $value)
    {
        return currency_format((float)$value);
    }

    /**
     * Validates a column type as a date
     */
    protected function validateDateTimeValue($value, $column)
    {
        $value = make_carbon($value);

        if (!$value instanceof Carbon) {
            throw new ApplicationException(sprintf(
                lang('admin::lang.list.invalid_column_datetime'), $column->columnName
            ));
        }

        return $value;
    }

    //
    // Filtering
    //

    public function addFilter(callable $filter)
    {
        $this->filterCallbacks[] = $filter;
    }

    //
    // Searching
    //

    /**
     * Applies a search term to the list results, searching will disable tree
     * view if a value is supplied.
     *
     * @param string $term
     */
    public function setSearchTerm($term)
    {
        $this->searchTerm = $term;
    }

    /**
     * Applies a search options to the list search.
     *
     * @param array $options
     */
    public function setSearchOptions($options = [])
    {
        extract(array_merge([
            'mode' => null,
            'scope' => null,
        ], $options));

        $this->searchMode = $mode;
        $this->searchScope = $scope;
    }

    /**
     * Returns a collection of columns which can be searched.
     * @return array
     */
    protected function getSearchableColumns()
    {
        $columns = $this->getColumns();
        $searchable = [];

        foreach ($columns as $column) {
            if (!$column->searchable) {
                continue;
            }

            $searchable[] = $column;
        }

        return $searchable;
    }

    /**
     * Applies the search constraint to a query.
     */
    protected function applySearchToQuery($query, $columns, $boolean = 'and')
    {
        $term = $this->searchTerm;

        if ($scopeMethod = $this->searchScope) {
            $searchMethod = $boolean == 'and' ? 'where' : 'orWhere';
            $query->$searchMethod(function ($q) use ($term, $scopeMethod) {
                $q->$scopeMethod($term);
            });
        }
        else {
            $searchMethod = $boolean == 'and' ? 'search' : 'orSearch';
            $query->$searchMethod($term, $columns, $this->searchMode);
        }
    }

    //
    // Sorting
    //

    /**
     * Event handler for sorting the list.
     */
    public function onSort()
    {
        if ($column = input('sort_by')) {
            // Toggle the sort direction and set the sorting column
            $sortOptions = [$this->getSortColumn(), $this->sortDirection];

            if ($column != $sortOptions[0] || strtolower($sortOptions[1]) == 'asc') {
                $this->sortDirection = $sortOptions[1] = 'desc';
            }
            else {
                $this->sortDirection = $sortOptions[1] = 'asc';
            }

            $this->sortColumn = $sortOptions[0] = $column;

            $this->putSession('sort', $sortOptions);

            // Persist the page number
            $this->currentPageNumber = input('page');

            return $this->onRefresh();
        }
    }

    /**
     * Returns the current sorting column, saved in a session or cached.
     */
    protected function getSortColumn()
    {
        if (!$this->isSortable())
            return false;

        if ($this->sortColumn !== null)
            return $this->sortColumn;

        // User preference
        if ($this->showSorting && ($sortOptions = $this->getSession('sort'))) {
            $this->sortColumn = $sortOptions[0];
            $this->sortDirection = $sortOptions[1];
        } // Supplied default
        else {
            if (is_string($this->defaultSort)) {
                $this->sortColumn = $this->defaultSort;
                $this->sortDirection = 'desc';
            }
            elseif (is_array($this->defaultSort) && isset($this->defaultSort[0])) {
                $this->sortColumn = $this->defaultSort[0];
                $this->sortDirection = (isset($this->defaultSort[1])) ? $this->defaultSort[1] : 'desc';
            }
        }

        // First available column
        if ($this->sortColumn === null || !$this->isSortable($this->sortColumn)) {
            $columns = $this->visibleColumns ?: $this->getVisibleColumns();
            $columns = array_filter($columns, function ($column) {
                return $column->sortable && $column->type != 'button';
            });
            $this->sortColumn = key($columns);
            $this->sortDirection = 'desc';
        }

        return $this->sortColumn;
    }

    /**
     * Returns true if the column can be sorted.
     *
     * @param null $column
     *
     * @return bool
     */
    protected function isSortable($column = null)
    {
        if ($column === null) {
            return count($this->getSortableColumns()) > 0;
        }
        else {
            return array_key_exists($column, $this->getSortableColumns());
        }
    }

    /**
     * Returns a collection of columns which are sortable.
     */
    protected function getSortableColumns()
    {
        if ($this->sortableColumns !== null) {
            return $this->sortableColumns;
        }

        $columns = $this->getColumns();
        $sortable = array_filter($columns, function ($column) {
            return $column->sortable;
        });

        return $this->sortableColumns = $sortable;
    }

    //
    // List Setup
    //

    /**
     * Event handler to display the list set up.
     */
    public function onLoadSetup()
    {
        $this->vars['columns'] = $this->getSetupListColumns();
        $this->vars['perPageOptions'] = $this->getSetupPerPageOptions();
        $this->vars['pageLimit'] = $this->pageLimit;

        $setupContentId = '#'.$this->getId().'-setup-modal-content';

        return [$setupContentId => $this->makePartial('lists/list_setup_form')];
    }

    /**
     * Event handler to apply the list set up.
     */
    public function onApplySetup()
    {
        if (($visibleColumns = post('visible_columns')) && is_array($visibleColumns)) {
            $this->columnOverride = $visibleColumns;
            $this->putSession('visible', $this->columnOverride);
        }

        $pageLimit = post('page_limit');
        $this->pageLimit = $pageLimit ? $pageLimit : $this->pageLimit;
        $this->putSession('order', post('column_order'));
        $this->putSession('page_limit', $this->pageLimit);

        return $this->onRefresh();
    }

    /**
     * Event handler to reset the list set up.
     */
    public function onResetSetup()
    {
        $this->resetSession();

        return $this->onRefresh();
    }

    /**
     * Returns all the list columns used for list set up.
     */
    protected function getSetupListColumns()
    {
        $columns = $this->defineListColumns();
        foreach ($columns as $column) {
            $column->invisible = true;
        }

        return array_merge($columns, $this->getVisibleColumns());
    }

    /**
     * Returns an array of allowable records per page.
     */
    protected function getSetupPerPageOptions()
    {
        $perPageOptions = [20, 40, 80, 100, 120];
        if (!in_array($this->pageLimit, $perPageOptions)) {
            $perPageOptions[] = $this->pageLimit;
        }

        return $perPageOptions;
    }

    //
    // Bulk Actions
    //

    public function onBulkAction()
    {
        $requestData = post();
        if (!strlen($code = array_get($requestData, 'code')))
            throw new ApplicationException(lang('admin::lang.list.missing_action_code'));

        $parts = explode('.', $code);
        $actionCode = array_shift($parts);
        if (!$bulkAction = array_get($this->getAvailableBulkActions(), $actionCode))
            throw new ApplicationException(sprintf(lang('admin::lang.list.action_not_found'), $actionCode));

        $checkedIds = array_get($requestData, 'checked');
        if (!$checkedIds || !is_array($checkedIds) || !count($checkedIds))
            throw new ApplicationException(lang('admin::lang.list.delete_empty'));

        $alias = post('alias') ?: $this->primaryAlias;

        $query = $this->prepareModel();

        $records = post('select_all') === '1'
            ? $query->get()
            : $query->whereIn($this->model->getKeyName(), $checkedIds)->get();

        $bulkAction->handleAction($requestData, $records);

        return $this->controller->refreshList($alias);
    }

    public function renderBulkActionButton($buttonObj)
    {
        if (is_string($buttonObj))
            return $buttonObj;

        $partialName = array_get(
            $buttonObj->config,
            'partial',
            'lists/list_action_button'
        );

        return $this->makePartial($partialName, ['button' => $buttonObj]);
    }

    protected function getAvailableBulkActions()
    {
        $this->fireSystemEvent('admin.list.extendBulkActions');

        $allBulkActions = $this->makeBulkActionButtons($this->bulkActions ?? []);
        $bulkActions = [];

        foreach ($allBulkActions as $actionCode => $buttonObj) {
            $bulkActions[$actionCode] = $this->makeBulkActionWidget($buttonObj);
        }

        return $this->availableBulkActions = $bulkActions;
    }

    protected function makeBulkActionButtons($bulkActions, $parentActionCode = null)
    {
        $result = [];
        foreach ($bulkActions as $actionCode => $config) {
            if ($parentActionCode)
                $actionCode = $parentActionCode.'.'.$actionCode;

            // Check if admin has permissions to show this column
            $permissions = array_get($config, 'permissions');
            if (!empty($permissions) && !AdminAuth::getUser()->hasPermission($permissions, false))
                continue;

            // Check that the filter scope matches the active location context
            if ($this->isLocationAware($config)) continue;

            $button = $this->makeBulkActionButton($actionCode, $config);

            $this->allBulkActions[$actionCode] = $result[$actionCode] = $button;
        }

        return $result;
    }

    protected function makeBulkActionButton($actionCode, $config)
    {
        $buttonType = array_get($config, 'type', 'link');

        $buttonObj = new ToolbarButton($actionCode);
        $buttonObj->displayAs($buttonType, $config);

        if ($buttonType === 'dropdown' && array_key_exists('menuItems', $config)) {
            $buttonObj->menuItems($this->makeBulkActionButtons($config['menuItems'], $actionCode));
        }

        return $buttonObj;
    }

    protected function makeBulkActionWidget($actionButton)
    {
        if (isset($this->bulkActionWidgets[$actionButton->name]))
            return $this->bulkActionWidgets[$actionButton->name];

        $widgetConfig = $this->makeConfig($actionButton->config);
        $widgetConfig['alias'] = $this->alias.studly_case('bulk_action_'.$actionButton->name);

        $actionCode = array_get($actionButton->config, 'code', $actionButton->name);
        $widgetClass = Widgets::instance()->resolveBulkActionWidget($actionCode);
        if (!class_exists($widgetClass))
            throw new Exception(sprintf(lang('admin::lang.alert_widget_class_name'), $widgetClass));

        $widget = new $widgetClass($this->controller, $actionButton, $widgetConfig);
        $widget->code = $actionButton->name;

        return $this->bulkActionWidgets[$actionButton->name] = $widget;
    }

    //
    // Helpers
    //

    /**
     * Check if column refers to a relation of the model
     *
     * @param ListColumn $column List column object
     * @param bool $multi If set, returns true only if the relation is a "multiple relation type"
     *
     * @return bool
     * @throws \Exception
     */
    protected function isColumnRelated($column, $multi = false)
    {
        if (!isset($column->relation) || $this->isColumnPivot($column)) {
            return false;
        }

        if (!$this->model->hasRelation($column->relation)) {
            throw new Exception(sprintf(lang('admin::lang.alert_missing_model_definition'), get_class($this->model), $column->relation));
        }

        if (!$multi) {
            return true;
        }

        $relationType = $this->model->getRelationType($column->relation);

        return in_array($relationType, [
            'hasMany',
            'belongsToMany',
            'morphToMany',
            'morphedByMany',
            'morphMany',
            'attachMany',
            'hasManyThrough',
        ]);
    }

    /**
     * Checks if a column refers to a pivot model specifically.
     *
     * @param ListColumn $column List column object
     *
     * @return bool
     */
    protected function isColumnPivot($column)
    {
        return isset($column->relation) && $column->relation == 'pivot';
    }
}
