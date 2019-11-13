import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { HcColumnControl } from 'DeskPRO/Bundle/PortalBundle/React/HcColumnControl';
import $ from 'jquery';

class ColumnControlWidget extends PageWidget {

  renderWidget() {
    const tablesData = window.DESKPRO_TICKET_LIST_TABLES;
    const $table = this.$element.closest('.dp-po-tickets-home');
    const $displayTable = $table.find('.dp-po-table-ticket');
    const $tableControls = $table.find('.dp-po-columnfilter');
    const $colControlButton = $tableControls.find('.dp-po-columnfilter-link');
    const $popup = $tableControls.find('.dp-po-columnfilter-dropdown');
    const table = tablesData[0];

    $colControlButton.click((e) => {
      e.preventDefault();
      e.stopPropagation();
      if ($popup.is(':visible')) {
        $popup.hide();
      } else {
        $popup.show();
      }
    });

    $(document).click((e) => {
      // if not a part of the popup, close it
      if (!$(e.target).closest('.dp-po-columnfilter-dropdown').length) {
        $popup.hide();
      }
    });

    function updateQueryStringParameter(uri, key, value) {
      let fragment = uri.match(/#.+$/);
      if (fragment) {
        fragment = fragment[0];
        uri = uri.replace(fragment, '');
      } else {
        fragment = '';
      }
      const re = new RegExp(`([?&])${key}=.*?(&|$)`, 'i');
      const separator = uri.indexOf('?') !== -1 ? '&' : '?';
      if (uri.match(re)) {
        return uri.replace(re, `$1${key}=${value}$2`) + fragment;
      }

      return `${uri + separator + key}=${value}${fragment}`;
    }

    function syncTableWithActiveColIds(activeColIds) {
      const newCols = activeColIds.join(',');

      $displayTable.find('[data-col]').each(function () {
        const $this = $(this);
        if ($.inArray($this.data('col'), activeColIds) < 0) {
          $this.hide();
        } else {
          $this.show();
        }
      });

      const colNumber = activeColIds.length + 1;

      $displayTable.each((index, t) => {
        const classNames = t.classList.values();

        for (const className of classNames) {
          if (className.match(/dp-po-table-col\d+/)) {
            t.classList.remove(className);
            t.classList.add(`dp-po-table-col${colNumber}`);
          }
        }
      });

      // setup pagination links, they need the updated selected cols
      const updateLinks = function () {
        $(this).attr('href', updateQueryStringParameter($(this).attr('href'), table.active_columns_param, newCols));
      };

      $('.dp-po-table-row-head a').each(updateLinks);
      $('.pagination a').each(updateLinks);

      const tlf = $('#ticket_list_search_form');

      let found = false;
      tlf.find('input[type=hidden]').each(function () {
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
    syncTableWithActiveColIds(table.active_columns);

    this.$rElement = $('<div class="dp-react-widget"></div>').appendTo($popup);
    ReactDOM.render(React.createElement(HcColumnControl, {
      columns:          table.columns,
      active_ids:       table.active_columns,
      updateActiveCols: syncTableWithActiveColIds
    }), this.$rElement.get(0));
  }
}

export class HcTicketList extends PageWidget {
  init() {
    this.addWidgetDef(ColumnControlWidget, '.dp-po-columnfilter-link');
  }
}
