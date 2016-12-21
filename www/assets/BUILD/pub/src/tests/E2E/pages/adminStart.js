import { url } from '../helpers';

const commands = {
  waitUntilLoaded() {
    return this
      .waitForElementVisible('@header')
      .assert.containsText('@header', 'DeskPRO has been installed successfully. But before you can start using your helpdesk you need to initialize a few options first.')
    ;
  }
};

module.exports = {
  url:      url('/admin/start'),
  commands: [commands],
  elements: {
    header: { selector: 'form > section.card-section:first-child > p' }
  }
};
