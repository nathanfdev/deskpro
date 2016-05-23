import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { ShareButton as ReactShareButton } from '../React/ShareButton';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import $ from 'jquery';

export class ShareButton extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<a href="#">' + portalPhrases.get('portal.general.btn-share') + '<i class="fa fa-share"></i></a>').insertAfter(this.$element);

    const component = React.createElement(ReactShareButton, {});
    this.$rElement.get(0).click(function (e) {
      console.log(component);
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
