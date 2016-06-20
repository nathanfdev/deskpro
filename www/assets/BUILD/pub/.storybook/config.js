import { configure } from '@kadira/storybook';

function loadStories() {
  require('../src/tests/visual');
}

configure(loadStories, module);
