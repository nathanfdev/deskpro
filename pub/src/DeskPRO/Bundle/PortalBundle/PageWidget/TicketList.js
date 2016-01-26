import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import ColumnControl from 'DeskPRO/Bundle/PortalBundle/React/ColumnControl';

class ColumnControlWidget extends PageWidget {
  renderWidget() {
    const tablesData = window.DESKPRO_TICKET_LIST_TABLES;
    const $table = this.$element.closest('.ticket-table');
    const $displayTable = $table.find('.user-ticket-list');
    const $tdTotalCols = $table.find('.span-total-cols');
    const $tableControls = $table.find('.table-controls');
    const $colControlButton = $tableControls.find('.column-control');
    const $popup = $tableControls.find('.popup-tiny');
    const tableId = $table.data('id');
    const table = tablesData[tableId];

    $colControlButton.click(function(e) {
      e.preventDefault();
      e.stopPropagation();
      if ($popup.is(':visible')) {
        $popup.hide();
      } else {
        $popup.show();
      }
    });

    $(document).click(function(e) {
      // if not a part of the popup, close it
      if (!$(e.target).closest('.popup-tiny').length) {
        $popup.hide();
      }
    });

    function updateQueryStringParameter(uri, key, value) {
      var re = new RegExp('([?&])' + key + '=.*?(&|$)', 'i');
      var separator = uri.indexOf('?') !== -1 ? '&' : '?';
      if (uri.match(re)) {
        return uri.replace(re, '$1' + key + '=' + value + '$2');
      }

      return uri + separator + key + '=' + value;
    }

    function sync_table_with_active_col_ids(activeColIds) {
      const newCols = activeColIds.join(',');

      const activeFilterLink = $displayTable.find('.dpx-active-filter-link');
      if (activeFilterLink.length > 0) {
        activeFilterLink.attr('href', updateQueryStringParameter(activeFilterLink.attr('href'), table.active_columns_param, newCols));
      }

      $displayTable.find('[data-col]').each(function() {
        const $this = $(this);
        if ($.inArray($this.data('col'), activeColIds) < 0) {
          $this.hide();
        } else {
          $this.show();
        }

        // setup pagination links, they need the updated selected cols
        var updateLinks = function() {
          $(this).attr('href', updateQueryStringParameter($(this).attr('href'), table.active_columns_param, newCols));
        };

        $('.table-header a').each(updateLinks);
        $('.pagination a').each(updateLinks);

        $tdTotalCols.attr('colspan', activeColIds.length + 1); // +1 for ticket ref (fixed)
      });

      const tlf = $('#ticket_list_search_form');

      let found = false;
      tlf.find('input[type=hidden]').each(function() {
        const $i = $(this);
        if ($i.attr('name') === table.active_columns.param) {
          $i.val(newCols);
          found = true;
        }
      });

      if (!found) {
        const newInput = $('<input type="hidden">');
        newInput.attr('name', table.active_columns_param).val(newCols);
        tlf.append(newInput);
      }
    }
    sync_table_with_active_col_ids(table.active_columns);

    this.$rElement = $('<div class="dp-react-widget"></div>').appendTo($popup);
    ReactDOM.render(React.createElement(ColumnControl, {
      columns: table.columns,
      active_ids: table.active_columns,
      updateActiveCols: sync_table_with_active_col_ids
    }), this.$rElement.get(0));
  }
}

export class TicketList extends PageWidget {
  init() {
    this.addWidgetDef(ColumnControlWidget, '.column-control');
  }
}
