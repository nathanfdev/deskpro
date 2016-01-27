import React from 'react';
import ReactDOM from 'react-dom';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { LoginDropdown } from '../React/Login/LoginDropdown';
import $ from 'jquery';

export class LoginDropdownWidget extends PageWidget {

  renderWidget() {
    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter(this.$element);
    // we will have some input from the response on the usersources
    const usersources = window.DESKPRO_USERSOURCES;

    ReactDOM.render(React.createElement(LoginDropdown, { usersources }), this.$rElement.get(0));
  }
}
