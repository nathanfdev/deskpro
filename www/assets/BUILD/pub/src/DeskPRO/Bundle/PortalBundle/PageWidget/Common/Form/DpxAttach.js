import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { PortalAttach } from '../../../React/Form/DropZone/PortalAttach';
import $ from 'jquery';

export class DpxAttach extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);

    const files = [];
    $(this.$element).find('.attach-row').each((i, file) => {
      const $file = $(file);
      files.push({
        info: {
          id:        $file.data('blob-id'),
          authcode:  $file.data('blob-auth'),
          filename:  $file.data('blob-filename'),
          url:       $file.data('blob-thumb-url'),
          size:      $file.data('blob-filesize'),
          icon_html: $file.find('.attach-row-icon').html()
        },
        errors: $file.find('.attach-row-errors').html().trim()
      });

      $file.remove();
    });

    const $input = this.$element.find('input[type=file]');
    const inputName = $input.attr('name').replace('[0][upload]', '');
    const component = React.createElement(PortalAttach, {
      widgetOptions: this.options,
      files,
      $input,
      inputName
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
