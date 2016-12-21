import { url } from '../helpers';

const commands = {
  waitUntilLoaded() {
    return this
      .waitForElementVisible('@sidebar')
      .waitForElementVisible('@dashboard')
      .assert.containsText('@dashboard', 'DeskPRO Updates')
      .assert.containsText('@sidebar', 'Admin Dashboard')
    ;
  }
};

module.exports = {
  url:      url('/admin/admin-interface'),
  commands: [commands],
  elements: {
    dashboard: { selector: 'div.dp-dashboard' },
    sidebar:   { selector: 'div.sidebar-list' }
  }
};
