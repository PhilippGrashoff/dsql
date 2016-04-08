<?php // vim:ts=4:sw=4:et:fdm=marker

namespace atk4\dsql;

/**
 * Perform a schema alteration query operation on SQL server (such as select, insert, delete, etc)
 */
class Migration extends Expression
{

    /**
     * addColumn() default type if 2nd argument is ommitted
     *
     * @var string|Expression
     */
    public $defaultColumnType = 'string';



    // {{{ Field specification and rendering
    /**
     * Alters table by adding a new column
     *
     * @param string|array            $field Create one or several fields in table
     * @param string|array|expression $type  Define type for this field(s)
     *
     * @return $this
     */
    public function addColumn($name, $type = null)
    {

        // Allow to specify comma-separated fields
        if (is_string($name)) {
            $name = array_map('trim',explode(',', $name));
        }

        if ($type === null) {
            $type = $this->defaultColumnType;
        }

        if (is_string($type) || $type instanceof Expresson) {
            $type = [$type];
        } elseif (is_array($type)) {
            if (!isset($type[0])) {

                if (is_array($this->defaultColumnType)) {
                    $type = array_merge($this->defaultColumnType, $type);
                }else{
                    $type[0] = $this->defaultColumnType;
                }
            }

        } else {
            throw new Exception(['Incorrect type declaration', 'type'=>$type]);
        }

        foreach($name as $n){
            $this->args['columns'][$n] = $type;
        }

        return $this;
    }

    public function changeColumn($name, $type = null)
    {
        if (is_string($name)) {
            $name = array_map('trim',explode(',', $name));
        }

        $this->addColumn($name,$type);

        foreach($name as $n){
            $this->args['columns'][$n]['change'] = true;
        }

        return $this;
    }

    public function dropColumn($name)
    {
        if (is_string($name) && strpos(',',$name) !== false) {
            $name = explode(',', $name);
        }

        if (!is_array($this->args['droppings'])) {
            $this->args['droppings'] = $name;
        } else {
            $this->args['droppings'] = array_merge(
                $this->args['droppings'], $name
            );
        }

        return $this;
    }

    function drop($option = null)
    {
        $this->template = $this->templates['drop'];
        $this['drop_options'] = $option?:"";
    }

    function create()
    {
    }


    /**
     * TO BE REFACTORED
     *
     * @return string Parsed template chunk
     */
    protected function _render_table_noalias()
    {
        // will be joined for output
        $ret = [];

        if ($this->args['table'] instanceof Expression) {
            throw new Exception('Table cannot be expression for UPDATE / INSERT queries in '.__METHOD__);
        }

        foreach ($this->args['table'] as $row) {
            list($table, ) = $row;

            $table = $this->_escape($table);

            $ret[] = $table;
        }

        return implode(', ', $ret);
    }

    /// }}}

    /**
     * Switch template for this query. Determines what would be done
     * on execute.
     *
     * By default it is in SELECT mode
     *
     * @param string $mode A key for $this->templates
     *
     * @return $this
     */
    public function selectTemplate($mode)
    {
        $this->mode = $mode;
        $this->template = $this->templates[$mode];

        return $this;
    }

}
