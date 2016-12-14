module.exports = {
  'I log in into agent interface and check some panes get loaded': (client) => {
    client.page.agentLogin().navigate().loginAsAdmin();
    client.waitForElementVisible('div#react_dp_agent_top_bar');
    client.waitForElementVisible('div#tickets_outline');
    client.waitForElementVisible('div#user-menu-popup');
    client.end();
  }
};
