import { url } from '../helpers.js';

function menuItemSelector(num) {
  return '//a[contains(@class, "dpw-app-bar-item-' + num + '")]/div/div[contains(@class, "icon")]';
}

module.exports = {
  'I go through the all apps in the agent interface and check navigation panes get loaded': function(client) {
    client.page.login().navigate().loginAsAdmin();

    client
      .useXpath()

      // Open agent interface root URL
      .url(url(''))

      // Wait for the navigation to load
      .waitForElementVisible('//nav[contains(@class, "dp-app-switcher")]')

      // Check we were redirected to /tasks
      .assert.urlEquals(url('/tasks'))

      // Go through the all menu items
      
      .click(menuItemSelector(1))
      .assert.urlEquals(url('/tickets'))
      .waitForElementVisible('//h1[contains(., "Tickets")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Inbox")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "All Tickets")]')

      .click(menuItemSelector(2))
      .assert.urlEquals(url('/crm'))
      .waitForElementVisible('//h1[contains(., "CRM")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "People")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Organizations")]')

      .click(menuItemSelector(3))
      .assert.urlEquals(url('/chat'))
      .waitForElementVisible('//h1[contains(., "Chat")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "My Chats")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "All Chats")]')

      .click(menuItemSelector(4))
      .assert.urlEquals(url('/feedback'))
      .waitForElementVisible('//h1[contains(., "Feedback")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Pending")]')

      .click(menuItemSelector(5))
      .assert.urlEquals(url('/publish'))
      .waitForElementVisible('//h1[contains(., "Publish")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Articles")]')

      .click(menuItemSelector(6))
      .assert.urlEquals(url('/tasks'))
      .waitForElementVisible('//h1[contains(., "Tasks")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Tasks")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Projects")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Agents")]')
    ;
    client.end();
  }
};
