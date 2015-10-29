import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import LoginDropdown from "DeskPRO/Bundle/PortalBundle/React/Login/LoginDropdown"
import $ from "jquery"
import React from "react"
import ReactDOM from "react-dom"

export default class LoginDropdownWidget extends PageWidget {
  renderWidget() {
    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter(this.$element);


    // we will have some input from the response on the usersources
    const usersources = [
      {
        id: 3,
        classes: ['button', 'auth-facebook'],
        text: 'Login with Facebook',
        icon: 'fa fa-facebook'
      }
    ];


    ReactDOM.render(React.createElement(LoginDropdown, { usersources }), this.$rElement.get(0));
  }
}
