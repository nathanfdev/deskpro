<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use Orb\Util\Strings;

/**
 * Class LayoutCollection.
 */
class LayoutCollection implements \Countable, \IteratorAggregate
{
    /**
     * Array of layouts keyed by some unique key.
     *
     * @var Layout[]
     */
    private $layouts = [];

    /**
     * Adds a layout to the collection.
     *
     * @param Layout $layout The layout to add
     * @param string $key    The layout key, or null
     *
     * @throws \OutOfBoundsException
     */
    public function addLayout($layout, $key)
    {
        if (isset($this->layouts[$key])) {
            throw new \OutOfBoundsException();
        }

        if ($key === null) {
            $key = '0';
        }

        $this->layouts[$key] = $layout;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasLayout($key)
    {
        return isset($this->layouts[$key]);
    }

    /**
     * @return bool
     */
    public function hasDefaultLayout()
    {
        return isset($this->layouts[0]);
    }

    /**
     * @param string $key
     *
     * @throws \InvalidArgumentException
     *
     * @return Layout
     */
    public function getLayout($key)
    {
        if (isset($this->layouts[$key])) {
            return $this->layouts[$key];
        } elseif (isset($this->layouts[0])) {
            return $this->layouts[0];
        }

        throw new \InvalidArgumentException('No layout exists');
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return Layout
     */
    public function getDefaultLayout()
    {
        if (!isset($this->layouts[0])) {
            throw new \InvalidArgumentException('No default layout exists');
        }

        return $this->layouts[0];
    }

    /**
     * @param bool $withReader
     *
     * @return string
     */
    public function compileJsObj($withReader = false)
    {
        $js = "(function () {\n";
        $js .= "\tvar layoutMap = {\n";

        $layoutCodes = [];
        foreach ($this->layouts as $k => $layout) {
            $k_str = "'$k'";

            $code          = trim(Strings::modifyLines($layout->compileJsObj(), "\t\t\t"));
            $bit_js        = "\t\t{$k_str}: {$code}";
            $layoutCodes[] = $bit_js;
        }

        $js .= implode(",\n", $layoutCodes)."\n";
        $js .= "\t};\n";

        if ($withReader) {
            $js .= "\n";
            $js .= "\tfunction getReader(ticket) {\n";
            $js .= "\t        var ticketReader;\n";
            $js .= "\t\n";
            $js .= "\t        // already our reader\n";
            $js .= "\t        if (ticket._is_sanitized_reader) {\n";
            $js .= "\t            return ticket;\n";
            $js .= "\t        }\n";
            $js .= "\t\n";
            $js .= "\t        // ticket is an object with getters\n";
            $js .= "\t        // use those getters + some cleaning/casting\n";
            $js .= "\t        if (typeof ticket.getDepartmentId === 'function') {\n";
            $js .= "\t            ticketReader = ticket;\n";
            $js .= "\t\n";
            $js .= "\t            // otherwise assume ticket is an object with all values\n";
            $js .= "\t        } else {\n";
            $js .= "\t            ticketReader = {\n";
            $js .= "\t                getDepartmentId:     function() { return ticket.department || ticket.department_id || 0; },\n";
            $js .= "\t                getCategoryId:       function() { return ticket.category   || ticket.category_id   || 0; },\n";
            $js .= "\t                getPriorityId:       function() { return ticket.priority   || ticket.priority_id   || 0; },\n";
            $js .= "\t                getProductId:        function() { return ticket.product    || ticket.product_id    || 0; },\n";
            $js .= "\t                getWorkflowId:       function() { return ticket.workflow   || ticket.workflow_id   || 0; },\n";
            $js .= "\t                getTicketFieldValue: function(fieldId) { return this._getFieldValue('ticket_field', 'ticket_fields', fieldId); },\n";
            $js .= "\t                getUserFieldValue:   function(fieldId) { return this._getFieldValue('user_field',   'user_fields',   fieldId); },\n";
            $js .= "\t                getOrgFieldValue:    function(fieldId) { return this._getFieldValue('org_field',    'org_fields',    fieldId); },\n";
            $js .= "\t                _getFieldValue: function(name, subName, fieldId) {\n";
            $js .= "\t                    var val = null;\n";
            $js .= "\t\n";
            $js .= "\t                    // e.g. ticket.ticket_field_123\n";
            $js .= "\t                    if (ticket[name + '_' + fieldId]) {\n";
            $js .= "\t                        val = ticket[name + '' + fieldId];\n";
            $js .= "\t\n";
            $js .= "\t                        // e.g. ticket.ticket_fields[123]\n";
            $js .= "\t                    } else if (ticket[subName] && ticket[subName][fieldId+'']) {\n";
            $js .= "\t                        val = ticket[subName][fieldId+''];\n";
            $js .= "\t                    }\n";
            $js .= "\t\n";
            $js .= "\t                    return val;\n";
            $js .= "\t                }\n";
            $js .= "\t            };\n";
            $js .= "\t	}\n";
            $js .= "\t\n";
            $js .= "\t	return {\n";
            $js .= "\t            getDepartmentId:     function() { return parseInt(ticketReader.getDepartmentId()) || 0; },\n";
            $js .= "\t            getCategoryId:       function() { return parseInt(ticketReader.getCategoryId())   || 0; },\n";
            $js .= "\t            getPriorityId:       function() { return parseInt(ticketReader.getPriorityId())   || 0; },\n";
            $js .= "\t            getProductId:        function() { return parseInt(ticketReader.getProductId())    || 0; },\n";
            $js .= "\t            getWorkflowId:       function() { return parseInt(ticketReader.getWorkflowId())   || 0; },\n";
            $js .= "\t            getTicketFieldValue: function(fieldId) { return this._cleanFieldValue(ticketReader.getTicketFieldValue(parseInt(fieldId))); },\n";
            $js .= "\t            getUserFieldValue:   function(fieldId) { return this._cleanFieldValue(ticketReader.getUserFieldValue(parseInt(fieldId))); },\n";
            $js .= "\t            getOrgFieldValue:    function(fieldId) { return this._cleanFieldValue(ticketReader.getOrgFieldValue(parseInt(fieldId))); },\n";
            $js .= "\t            _cleanFieldValue: function(val) {\n";
            $js .= "\t                // return as is for now\n";
            $js .= "\t                return val;\n";
            $js .= "\t            },\n";
            $js .= "\t            _is_sanitized_reader: true\n";
            $js .= "\t	}\n";
            $js .= "\t};\n";
            $js .= "\n";
        }

        $js .= "\treturn {\n";
        $js .= "\t\tgetLayout: function (departmentId) {\n";
        $js .= "\t\t\treturn layoutMap[departmentId+''] || layoutMap['0'] || null;\n";

        if ($withReader) {
            $js .= "\t\t},\n";
            $js .= "\t\tgetLayoutFields: function(departmentId) {\n";
            $js .= "\t\t\tvar layout = this.getLayout(departmentId);\n";
            $js .= "\t\t\tif (!layout) {\n";
            $js .= "\t\t\t\treturn null;\n";
            $js .= "\t\t\t}\n";
            $js .= "\t\t\n";
            $js .= "\t\t\treturn layout.getFields();\n";
            $js .= "\t\t},\n";
            $js .= "\t\tgetMatchingFields: function(ticket, asString) {\n";
            $js .= "\t\t\tticket = getReader(ticket);\n";
            $js .= "\t\t\tvar layout = this.getLayout(ticket.getDepartmentId());\n";
            $js .= "\t\t\tif (!layout) {\n";
            $js .= "\t\t\t\treturn asString ? '': null;\n";
            $js .= "\t\t\t}\n";
            $js .= "\t\t\n";
            $js .= "\t\t\treturn layout.getMatchingFields(ticket, asString);\n";
        }

        $js .= "\t\t}\n";

        $js .= "\t};\n";

        $js .= '})()';

        return $js;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->layouts);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->layouts);
    }
}
