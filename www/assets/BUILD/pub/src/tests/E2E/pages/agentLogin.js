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
  url:      url('/agent/login'),
  commands: [commands],
  elements: {
    loginInput:    { selector: '#normal_view > div.content > form input[name="email"]' },
    passwordInput: { selector: '#normal_view > div.content > form input[type="password"]' },
    loginButton:   { selector: '#normal_view > div.content > form input[type="submit"]' }
  }
};
