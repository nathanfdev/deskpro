import { url } from '../helpers';

const commands = {
  waitUntilLoaded() {
    return this
      .waitForElementVisible('@header')
      .assert.containsText('@header', 'Admin Account')
    ;
  }
};

module.exports = {
  url:      url('/admin/start'),
  commands: [commands],
  elements: {
    header: { selector: 'section.card-section h3' }
  }
};
