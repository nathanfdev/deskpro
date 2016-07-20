/**
 * This replaces buggy "import { action } from '@kadira/storybook';"
 *
 * This bug https://github.com/kadirahq/react-storybook/issues/174 appears when importing storybook's action in Jest.
 *
 * @param name
 * @returns {Function}
 */
export function action(name) {
  return function (...args) {
    console.log('Action: ', name, ', args: ', args);
  }
}
