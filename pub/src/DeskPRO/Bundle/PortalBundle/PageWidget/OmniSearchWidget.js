import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import OmniSearch from "DeskPRO/Bundle/PortalBundle/React/OmniSearch/OmniSearch"
import $ from "jquery"
import React from "react"
import ReactDOM from "react-dom"

export default class OmniSearchWidget extends PageWidget {
  renderWidget() {
    this.$rElement = $('<div class="dp-react-widget"></div>').appendTo(this.$element);
    ReactDOM.render(React.createElement(OmniSearch, {
      input: this.$element.find('input'),
      close: this.$element.find('.search-clear')
    }), this.$rElement.get(0));
    const $input = this.$element.find('input');
    const $x = this.$element.find('.search-clear');
    $('.omnisearch-link').each(function(){
        $(this).click(function(e){
            e.preventDefault();
            $input.val($(this).text()).change().focus();
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
