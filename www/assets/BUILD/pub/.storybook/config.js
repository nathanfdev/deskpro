import { configure } from '@kadira/storybook';
import '../../web/app-build/styles/Admin/admin-style.css';
import '../../web/stylesheets-less/semantic-ui/semantic.css';
import '../src/tests/Visual/Resources/storybook-style-fix.css';

function loadStories() {
  require('../src/tests/Visual');
}

configure(loadStories, module);
