import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import PortalAttach from '../../../React/Form/DropZone/PortalAttach';

export default class DpxAttach extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);

    const files = [];
    $(this.$element).find('.attach-row').each((i, file) => {
      const $file = $(file);
      const errors = $file.find('.attach-row-errors').html();

      files.push({
        info: {
          id:        $file.data('blob-id'),
          authcode:  $file.data('blob-auth'),
          filename:  $file.data('blob-filename'),
          url:       $file.data('blob-thumb-url'),
          size:      $file.data('blob-filesize'),
          icon_html: $file.find('.attach-row-icon').html()
        },
        errors: errors ? errors.trim() : ''
      });

      $file.remove();
    });

    const $input = $(this.$element.find(':not([data-prototype=""])').data('prototype')).find('input[type=file]');
    const $form = $(this.$element).closest('form');
    const inputName = $input.attr('name') ? $input.attr('name').replace('[__name__][blob][upload]', '') : null;
    const maxFileSize = this.$element.data('maxFileSize') || null;
    const uploadUrl = this.$element.data('uploadUrl') || undefined;
    const component = React.createElement(PortalAttach, {
      widgetOptions: this.options,
      files,
      $input,
      inputName,
      maxFileSize,
      $form,
      uploadUrl,
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
