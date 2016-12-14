import { config } from '../config';
import { url } from '../helpers';

const commands = {
  login(login, password) {
    return this
      .waitForElementVisible('@loginInput')
      .setValue('@loginInput', login)
      .setValue('@passwordInput', password)
      .click('@loginButton')
      .waitForElementVisible('@sidebar')
      .assert.urlEquals(url('/agent/'))
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
    loginInput:    { selector: 'input[name="email"]' },
    passwordInput: { selector: 'input[type="password"]' },
    loginButton:   { selector: 'input[type="submit"]' },
    sidebar:       { selector: 'div#react_dp_side_bar_container' }
  }
};
