import { url } from '../helpers';

const commands = {
  waitUntilLoaded() {
    return this
      .waitForElementVisible('@header')
      .waitForElementVisible('@admin')
      .waitForElementVisible('@ticket')
      .assert.containsText('@admin', 'ADMIN')
      .assert.containsText('@ticket', 'CONTACT US')
      .click('@admin')
      .waitForElementVisible('@adminDropdown', 100)
    ;
  }
};

module.exports = {
  url:      url('/'),
  commands: [commands],
  elements: {
    header:        { selector: 'div.brand h1' },
    admin:         { selector: 'a#admin-dropdown-arrow span.datb-interface-button-title' },
    adminDropdown: { selector: 'div#agent-bar-admin-dropdown' },
    ticket:        { selector: 'div.search-and-ticket a[href="/new-ticket"]' }
  }
};
