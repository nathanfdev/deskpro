import { url } from '../helpers';

const commands = {
  waitUntilLoaded() {
    return this
      .waitForElementVisible('@header')
      .waitForElementVisible('@admin')
      .waitForElementVisible('@ticket')
      .assert.containsText('@header', 'Helpdesk')
      .assert.containsText('@admin', 'ADMIN')
      .assert.containsText('@ticket', 'CONTACT US')
    ;
  }
};

module.exports = {
  url:      url('/en'),
  commands: [commands],
  elements: {
    header: { selector: 'div.brand h1' },
    admin:  { selector: 'a#admin-dropdown-arrow span.datb-interface-button-title' },
    ticket: { selector: 'div.search-and-ticket a[href="/en/new-ticket"]' }
  }
};
