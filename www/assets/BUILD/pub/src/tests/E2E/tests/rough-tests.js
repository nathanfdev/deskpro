module.exports = {
  'I log in into agent interface and check some panes get loaded': (client) => {
    client.deleteCookies().page.agentLogin().navigate().loginAsAdmin();
    client.page.agent().navigate().load();
    client.end();
  },
  'I log in into admin interface and check it get loaded': (client) => {
    client.deleteCookies().page.adminLogin().navigate().loginAsAdmin();
    client.page.admin().navigate().load();
    client.end();
  }
};
