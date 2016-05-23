import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { ShareButton as ReactShareButton } from '../React/ShareButton';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import $ from 'jquery';

export class ShareWidget extends PageWidget {

  renderWidget() {
    this.$rElement = $('<div></div>').insertAfter(this.$element);

    const component = React.createElement(ReactShareButton, {});
    this.$element.click(function (e) {
      console.log(component);
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
