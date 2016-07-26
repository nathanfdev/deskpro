import { url } from '../helpers.js';

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

      // Click on the link #1
      .click('//a[contains(@class, "dpw-app-bar-item-1")]')
      .assert.urlEquals(url('/tickets'))
      .waitForElementVisible('//h1[contains(., "Tickets")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Inbox")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "All Tickets")]')

      // Click on the link #2
      .click('//a[contains(@class, "dpw-app-bar-item-2")]')
      .assert.urlEquals(url('/crm'))
      .waitForElementVisible('//h1[contains(., "CRM")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "People")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Organizations")]')

      // Click on the link #3
      .click('//a[contains(@class, "dpw-app-bar-item-3")]')
      .assert.urlEquals(url('/chat'))
      .waitForElementVisible('//h1[contains(., "Chat")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "My Chats")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "All Chats")]')

      // Click on the link #4
      .click('//a[contains(@class, "dpw-app-bar-item-4")]')
      .assert.urlEquals(url('/feedback'))
      .waitForElementVisible('//h1[contains(., "Feedback")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Pending")]')

      // Click on the link #5
      .click('//a[contains(@class, "dpw-app-bar-item-5")]')
      .assert.urlEquals(url('/publish'))
      .waitForElementVisible('//h1[contains(., "Publish")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Articles")]')

      // Click on the link #6
      .click('//a[contains(@class, "dpw-app-bar-item-6")]')
      .assert.urlEquals(url('/tasks'))
      .waitForElementVisible('//h1[contains(., "Tasks")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Tasks")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Projects")]')
      .waitForElementVisible('//div[contains(@class, "list-sidebar-title") and contains(., "Agents")]')
    ;
    client.end();
  }
};
