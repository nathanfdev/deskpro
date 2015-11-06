import React from "react";
import ReactDOM from "react-dom";
import $ from "jquery";
import _ from "lodash";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import ColumnControl from "DeskPRO/Bundle/PortalBundle/React/ColumnControl";


//######################################################################################################################
//# Column Control Widget
//######################################################################################################################

class ColumnControlWidget extends PageWidget {
  renderWidget() {
    const tables_data = window.DESKPRO_TICKET_LIST_TABLES;
    const $table = this.$element.closest('.ticket-table');
    const $display_table = $table.find('.user-ticket-list');
    const $pagination = $table.find('.pagination');
    const $td_total_cols = $table.find('.span-total-cols');
    const $table_controls = $table.find('.table-controls');
    const $col_control_button = $table_controls.find('.column-control');
    const $popup = $table_controls.find('.popup-tiny');
    const table_id = $table.data('id');
    const table = tables_data[table_id];

    $col_control_button.click(function(e) {
      e.preventDefault();
      if ($popup.is(':visible')) {
        $popup.hide();
      } else {
        $popup.show();
      }
    });

    function sync_table_with_active_col_ids(active_col_ids) {
      $display_table.find('[data-col]').each(function() {
        const $this = $(this);
        if ($.inArray($this.data('col'), active_col_ids) < 0) {
          $this.hide();
        } else {
          $this.show();
        }

        function updateQueryStringParameter(uri, key, value) {
          var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
          var separator = uri.indexOf('?') !== -1 ? "&" : "?";
          if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
          }
          else {
            return uri + separator + key + "=" + value;
          }
        }

        // setup pagination links, they need the updated selected cols
        const new_cols = active_col_ids.join(',');
        $('.pagination a').each(function() {
          $(this).attr('href', updateQueryStringParameter($(this).attr('href'), table.active_columns_param, new_cols));
        });

        $td_total_cols.attr('colspan', active_col_ids.length);
      });
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


//######################################################################################################################
//# Page widget
//######################################################################################################################

export default class TicketList extends PageWidget {
  init() {
    this.addWidgetDef(ColumnControlWidget, ".column-control");
  }
}
