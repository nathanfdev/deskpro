import ReactDOM from 'react-dom';
import React from 'react';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { DownloadPopup } from '../React/DownloadPopup';
import $ from 'jquery';

export class DownloadPopupWidget extends PageWidget {

  renderWidget() {
    const $el = this.$element;
    const $parent = $el.closest('.download-item');

    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').appendTo($el.closest('body'));

    const component = React.createElement(DownloadPopup, {
      filename:      $el.data('filename'),
      filesize:      $el.data('filesize'),
      dateUploaded:  $el.data('date-uploaded'),
      downloadUrl:   $el.attr('href'),
      voteUrl:       $el.data('vote-url'),
      voteCount:     $el.data('vote-count'),
      $voteWidget:   $parent.find('.as-vote-widget'),
      $button:       this.$element,
      widgetOptions: this.options
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
