import { configure } from '@kadira/storybook';

function loadStories() {
  require('../src/tests/Visual');
}

configure(loadStories, module);
