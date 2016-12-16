import { url } from '../helpers';

const commands = {
  waitUntilLoaded() {
    return this
      .waitForElementVisible('@sidebar')
      .waitForElementVisible('@dashboard')
      .waitForElementVisible('@updateButton')
      .assert.containsText('@dashboard', 'DeskPRO Updates')
      .assert.containsText('@sidebar', 'Admin Dashboard')
      .assert.containsText('@updateButton', 'Update DeskPRO Now →')
    ;
  }
};

module.exports = {
  url:      url('/admin/admin-interface'),
  commands: [commands],
  elements: {
    updateButton: { selector: 'div.status-content a[href="#/setup/updater"]' },
    dashboard:    { selector: 'div.dp-dashboard' },
    sidebar:      { selector: 'div.sidebar-list' }
  }
};
