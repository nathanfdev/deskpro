module.exports = {
  'I try to access secured area not being logged in': function (client) {
    client.deleteCookies();
    client.page.tickets().navigate().waitForElementVisible('.dpw-login-form').assert.urlEquals('http://dp.lo/new-agent/login');
    client.end();
  },

  'I log in as admin and access secured area': function (client) {
    client.page.login().navigate().login('admin@dp.lo', '111111');
    client.page.tickets().navigate().waitForElementVisible('@myTicketsFilter');
    client.end();
  }
};
