import { url } from '../helpers';

const commands = {
  load() {
    return this
      .waitForElementVisible('@dashboard', 30000)
      .waitForElementVisible('@sidebar')
      .assert.containsText('@dashboard', 'DeskPRO Updates')
      .assert.containsText('@sidebar', 'Admin Dashboard')
    ;
  }
};

module.exports = {
  url:      url('/admin/admin-interface'),
  commands: [commands],
  elements: {
    updateButton: { selector: 'a[href="#/setup/updater"]' },
    dashboard:    { selector: 'div.dp-dashboard' },
    sidebar:      { selector: 'div.sidebar-list' }
  }
};
