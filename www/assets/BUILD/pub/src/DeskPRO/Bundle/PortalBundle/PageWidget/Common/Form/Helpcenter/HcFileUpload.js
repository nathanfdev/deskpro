import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { FileUploadInput } from '@deskpro/portal-components';

export default class HcFileUpload extends PageWidget {

  renderWidget() {
    console.log(this.$element);
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget dp-pc_field as-dpui"></div>').insertAfter(this.$element);

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

    const uploadUrl = this.$element.data('uploadUrl') || undefined;
    const component = React.createElement(FileUploadInput, {
      url:      uploadUrl,
      multiple: true,
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
