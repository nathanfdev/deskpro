import { config } from '../../config.js';
import { url } from '../../helpers.js';

module.exports = {
  'I load "My Tickets"': function(client) {
    client.page.login().navigate().loginAsAdmin();
    client.page.tickets().navigate()

      // Wait for navigation filters list
      .waitForElementVisible('@myTicketsFilter')

      // After loading there should be no spinner or cards
      .assert.elementNotPresent('@listSpinner')
      .assert.elementNotPresent('@listCard')

      // After clicking on the menu item spinner should be visible
      .click('@myTicketsFilter')
      .waitForElementVisible('@listSpinner')
      .assert.elementNotPresent('@listCard')

      // And URL must contain My_Tickets
      .assert.urlContains('My_Tickets')

      // After list is loaded there should be cards and spinner should not be visible
      .waitForElementVisible('@listCard')
      .assert.elementNotPresent('@listSpinner')
    ;
    client.end();
  }
};
