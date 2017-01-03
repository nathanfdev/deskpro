import { config } from '../config';
import { url } from '../helpers';

const commands = {
  login(login, password) {
    return this
      .waitForElementVisible('@loginInput')
      .setValue('@loginInput', login)
      .setValue('@passwordInput', password)
      .click('@loginButton')
      .waitForElementNotPresent('@loginButton')
      ;
  },
  loginAsAdmin() {
    this.login(
      config.users.admin.email,
      config.users.admin.password
    );
  }
};

module.exports = {
  url:      url('/en/login'),
  commands: [commands],
  elements: {
    loginInput:    { selector: 'form#login input[name="username"]' },
    passwordInput: { selector: 'form#login input[type="password"]' },
    loginButton:   { selector: 'form#login button.button' }
  }
};
