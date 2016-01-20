import React from 'react';
import ReactDOM from 'react-dom';
import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';
import OmniSearch from 'DeskPRO/Bundle/PortalBundle/React/OmniSearch/OmniSearch';
import $ from 'jquery';

export default class OmniSearchWidget extends PageWidget {
  renderWidget() {
    this.$rElement = $('<div class="dp-react-widget"></div>').appendTo(this.$element);
    ReactDOM.render(React.createElement(OmniSearch, {
      input: this.$element.find('input'),
      close: this.$element.find('.search-clear')
    }), this.$rElement.get(0));
    const $input = this.$element.find('input.omnisearch');
    const $x = this.$element.find('.search-clear');
    $('.dpx-omnisearch-link').each(function(){
        $(this).click(function(e){
          e.preventDefault();
          const term = $(this).text();
          $input.val('').change(); // clear exiting results, if any
          $input.val(term);
          $input.change().focus();
        });
    });
    setInterval(() => {
      if ($input.val().length > 0) {
        $x.show();
      } else {
        $x.hide();
      }
    }, 250);
  }
}
