import ReactDOM from 'react-dom';
import React from 'react';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { DownloadPopup } from '../React/DownloadPopup';
import $ from 'jquery';

export class DownloadPopupWidget extends PageWidget {

  renderWidget() {
    const $el = this.$element;
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').appendTo($el.closest('body'));

    const component = React.createElement(DownloadPopup, {
      filename: $el.data('filename'),
      filesize: $el.data('filesize'),
      dateUploaded: $el.data('date-uploaded'),
      downloadUrl: $el.attr('href'),
      voteUrl: $el.attr('vote-url'),
      voteCount: $el.attr('vote-count'),
      $button: this.$element,
      widgetOptions: this.options
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
