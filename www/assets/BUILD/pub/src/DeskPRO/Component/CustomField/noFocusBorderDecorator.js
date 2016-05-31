import ReactDOM from 'react-dom';
import $ from 'jquery';

/**
 * This prevents the focus border when just clicking on an item,
 * but leaves it there if you focus it via kbd
 *
 * @param component
 */
export function noFocusBorder(component) {
  component.prototype.addNoFocusBorderListeners = node => {
    const $el = $(ReactDOM.findDOMNode(node));

    $el.on('mousedown', () => $el.addClass('no-focus-border'));
    $el.on('blur', () => $el.removeClass('no-focus-border'));
    $el.on('keydown', event => {
      if (event.which === 32) {
        event.preventDefault();
      }
    });
  };
}
