module.exports = {
  'I log in into agent interface and check some panes get loaded': (client) => {
    client.page.agentLogin().navigate().loginAsAdmin();
    client.page.agent().navigate().load();
    client.end();
  },
  'I log in into agent interface and check admin get loaded': (client) => {
    client.page.adminLogin().navigate().loginAsAdmin();
    client.page.admin().navigate().load();
    client.end();
  }
};
