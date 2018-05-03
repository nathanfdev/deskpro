import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import PortalAttach from '../../../React/Form/DropZone/PortalAttach';

export default class DpxCustomFieldAttach extends PageWidget {

  renderWidget() {
    const $el = this.$element;
    const $form = $(this.$element).closest('form');

    $el.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);

    const $input = $(this.$element.find(':not([data-prototype=""])').data('prototype'));
    const inputName = $input.attr('name') ? $input.attr('name').replace('[__name__]', '') : null;
    const maxFileSize = $el.data('max-file-size');
    const extensionsLimitMode = $el.data('extensions-limit-mode');
    const mustExtensions = $el.data('must-extensions');
    const notExtensions = $el.data('not-extensions');
    const multiple = !!$el.data('multiple');
    const restrictionSet = $el.data('restriction-set');

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

    const component = React.createElement(PortalAttach, {
      widgetOptions: this.options,
      customField:   true,
      uploadUrl:     `dpblob/${restrictionSet}`,
      files,
      $input,
      inputName,
      multiple,
      maxFileSize,
      extensionsLimitMode,
      mustExtensions,
      notExtensions,
      $form
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
