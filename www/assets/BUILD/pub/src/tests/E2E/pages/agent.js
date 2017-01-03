import { url } from '../helpers';

const commands = {
  waitUntilLoaded() {
    return this
      .waitForElementVisible('@sidebar')
      .waitForElementVisible('@topbar')
    ;
  }
};

module.exports = {
  url:      url('/admin/admin-interface'),
  commands: [commands],
  elements: {
    sidebar: { selector: 'div#react_dp_side_bar_container' },
    topbar:  { selector: 'div#react_dp_agent_top_bar' }
  }
};
