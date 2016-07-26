import { configure } from '@kadira/storybook';
import '../app-build/styles/Admin/admin-style.css';
import '../stylesheets-less/semantic-ui/semantic.css';

function loadStories() {
  require('../es6/tests/Visual');
}

configure(loadStories, module);
