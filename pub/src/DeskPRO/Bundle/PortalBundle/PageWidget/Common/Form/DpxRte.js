import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { PortalRte } from '../../../React/Form/PortalRte';
import $ from 'jquery';

/**
 * A DpxRte takes three fields:
 *
 *  textarea -- the main form element
 *  html     -- a hidden form element containing HTML code
 *  format   -- a hidden form element containing the edit mode we're in (text/html)
 *
 *  If this browser is able to use the RTE, then we hide the txt field,
 *  show the html field, and set the format to html
 */
export class DpxRte extends PageWidget {

  renderWidget() {
    const $textTextarea = this.$element.find('textarea[data-rte-field="text"]');
    const $format = this.$element.find('input[data-rte-field="format"]');
    const $rElement = $('<div class="dp-medium-rte-wrapper as-dpui"></div>').appendTo(this.$element);

    $textTextarea.hide();
    $format.val('html');

    const component = React.createElement(PortalRte, {
      className: 'dp-medium-rte medium-editor-placeholder',
      $textTextarea,
      widgetOptions: this.options,
      $toolbarContainer: $rElement
    });

    ReactDOM.render(component, $rElement.get(0));
  }
}
