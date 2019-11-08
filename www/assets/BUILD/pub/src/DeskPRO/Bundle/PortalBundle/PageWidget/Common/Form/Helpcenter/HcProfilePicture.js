import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { HcProfilePictureInput } from './HcProfilePictureInput';

export default class HcProfilePicture extends PageWidget {

  onChange = (name, files) => {
    this.input.trigger('blobs', [files]);
  };

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget dp-pc_field as-dpui"></div>').insertAfter(this.$element);

    this.input = this.$element.find('input');

    const inputName = this.input.attr('name') ? this.input.attr('name').replace(/\[\d+\]\[blob\]\[upload\]/, '') : null;

    const inputId = this.input.attr('id');

    const uploadUrl = this.$element.data('uploadUrl') || undefined;

    this.input.remove();

    let csrfToken = null;
    if (window.dp_get_csrf_token) {
      csrfToken = window.dp_get_csrf_token();
    }

    const component = React.createElement(HcProfilePictureInput, {
      name:     inputName,
      url:      uploadUrl,
      id:       inputId,
      csrfToken,
      onChange: this.onChange,
      multiple: false,
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
