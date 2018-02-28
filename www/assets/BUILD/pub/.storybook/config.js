import { configure } from '@storybook/react';
import '../../web/stylesheets-less/semantic-ui/semantic.css';
import '../src/tests/Visual/Resources/storybook-style-fix.css';

function loadStories() {
  require('../src/tests/Visual');
}

configure(loadStories, module);
