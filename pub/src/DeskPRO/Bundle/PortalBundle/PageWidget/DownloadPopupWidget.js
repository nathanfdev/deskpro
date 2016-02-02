import ReactDOM from 'react-dom';
import React from 'react';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { DownloadPopup } from '../React/DownloadPopup';
import $ from 'jquery';

export class DownloadPopupWidget extends PageWidget {

  renderWidget() {
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').appendTo(this.$element.closest('body'));
    const component = React.createElement(DownloadPopup, {
      filename: 'Admin Quick Launch Guide.pdf',
      filesize: '125kb',
      dateUploaded: '2016-02-02',
      $button: this.$element,
      widgetOptions: this.options
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
