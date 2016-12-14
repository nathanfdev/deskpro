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
  url:      url('/agent/login?return=/admin/admin-interface'),
  commands: [commands],
  elements: {
    loginInput:    { selector: 'input[name="email"]' },
    passwordInput: { selector: 'input[type="password"]' },
    loginButton:   { selector: 'input[type="submit"]' }
  }
};
