import { configure } from '@kadira/storybook';
import '../app-build/less.css';
import '../app-build/styles.css';
import '../stylesheets-less/semantic-ui/semantic.css';

function loadStories() {
  require('../es6/tests/Visual');
}

configure(loadStories, module);
