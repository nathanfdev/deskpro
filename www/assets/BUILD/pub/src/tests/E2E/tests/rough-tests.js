module.exports = {
  'I log in and check agent': (client) => {
    client.deleteCookies().page.agentLogin().navigate().loginAsAdmin();
    client.page.agent().waitUntilLoaded();
    client.end();
  },

  'I log in and check admin': (client) => {
    client.deleteCookies().page.adminLogin().navigate().loginAsAdmin();
    client.page.admin().waitUntilLoaded();
    client.end();
  },

  'I log in and check admin/start': (client) => {
    client.deleteCookies().page.adminStartLogin().navigate().loginAsAdmin();
    client.page.adminStart().waitUntilLoaded();
    client.end();
  },

  'I log in and check portal admin dropdown menu is expandable': (client) => {
    client.deleteCookies().page.login().navigate().loginAsAdmin();
    const portal = client.page.portal();

    portal.waitUntilLoaded();

    portal
      .assert.hidden('@adminDropdownBody')
      .click('@adminDropdownArrow')
      .waitForElementVisible('@adminDropdownBody', 100);

    client.end();
  }
};
