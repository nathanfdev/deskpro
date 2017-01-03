import { url } from '../helpers';

const commands = {
  waitUntilLoaded() {
    return this
      .waitForElementVisible('@header')
      .waitForElementVisible('@ticket')
      .assert.containsText('@ticket', 'CONTACT US')
    ;
  }
};

module.exports = {
  url:      url('/'),
  commands: [commands],
  elements: {
    header:             { selector: 'div.brand h1' },
    adminDropdownArrow: { selector: 'a#admin-dropdown-arrow span.datb-interface-button-title' },
    adminDropdownBody:  { selector: 'div#agent-bar-admin-dropdown' },
    ticket:             { selector: 'div.search-and-ticket a[href="/new-ticket"]' }
  }
};
