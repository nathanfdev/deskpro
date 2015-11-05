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
    const $column_control = this.$element;
    const columns = $column_control.data('columns').split(',');
    const active_columns = $column_control.data('active-columns').split(',');
    const $table = $column_control.closest('.ticket-table').find('.user-ticket-list');

    function hide_inactive(active) {
      $table.find('[data-col]').each(function() {
        const $this = $(this);
        if ($.inArray($this.data('col'), active) < 0) {
          $this.hide();
        } else {
          $this.show();
        }
      });
    }
    hide_inactive(active_columns);

    this.$rElement = $('<div class="dp-react-widget"></div>').appendTo($column_control.closest('.table-controls').find('.popup-tiny'));
    ReactDOM.render(React.createElement(ColumnControl, {
      columns: columns,
      active: active_columns,
      updateActiveCols: hide_inactive
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
