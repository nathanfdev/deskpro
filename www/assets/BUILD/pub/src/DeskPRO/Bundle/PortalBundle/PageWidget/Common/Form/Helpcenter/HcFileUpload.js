import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { FileUploadInput } from '@deskpro/portal-components';

export default class HcFileUpload extends PageWidget {

  onChange = (name, files) => {
    this.input.trigger('blobs', [files]);
  };

  renderWidget() {
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

    this.input = this.$element.find('input');

    const inputName = this.input.attr('name') ? this.input.attr('name').replace(/\[\d+\]\[blob\]\[upload\]/, '') : null;

    const inputId = this.input.attr('id');

    const uploadUrl = this.$element.data('uploadUrl') || undefined;

    this.input.remove();

    let csrfToken = null;
    if (window.dp_get_csrf_token) {
      csrfToken = window.dp_get_csrf_token();
    }

    const component = React.createElement(FileUploadInput, {
      name:     inputName,
      url:      uploadUrl,
      id:       inputId,
      files,
      csrfToken,
      onChange: this.onChange,
      multiple: true,
      i18n:     {
        dragNDrop:   portalPhrases.get('helpcenter.forms.label_drag_and_drop'),
        or:          portalPhrases.get('helpcenter.general.or'),
        chooseAFile: portalPhrases.get('helpcenter.forms.label_choose_a_file'),
        chooseFiles: portalPhrases.get('helpcenter.forms.label_choose_files'),
      }
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
