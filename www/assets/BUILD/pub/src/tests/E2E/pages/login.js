import { url } from '../helpers.js';

const commands = {
  login: function(login, password) {
    return this
      .waitForElementVisible('@loginInput')
      .setValue('@loginInput', login)
      .setValue('@passwordInput', password)
      .click('@loginButton')
      .waitForElementVisible('@loadingIndicator')
    ;
  }
};

module.exports = {
  url: url('/login'),
  commands: [commands],
  elements: {
    loginInput:       {selector: 'input[type=text]'},
    passwordInput:    {selector: 'input[type=password]'},
    loginButton:      {selector: 'input[type=submit]'},
    loadingIndicator: {selector: 'div.deskpro-loading'},
  }
};
