import { configure } from '@kadira/storybook';
import '../app-build/less.css';

function loadStories() {
  require('../es6/tests/Visual');
}

configure(loadStories, module);
