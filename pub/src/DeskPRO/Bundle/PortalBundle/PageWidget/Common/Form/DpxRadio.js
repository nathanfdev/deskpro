import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';

export default class DpxRadio extends PageWidget {
  renderWidget() {
    const $el = this.$element;
    const $radio = $el.find('input');

    $el.attr('tabindex', '0');

    // This prevents the focus border when just clicking on an item,
    // but leaves it there if you focus it via kbd
    $el.on('mousedown', () => {
      $el.addClass('no-focus-border');
      $radio.prop('checked', true);
    });

    $el.on('blur', () => $el.removeClass('no-focus-border'));
    $el.on('keydown', (ev) => {
      if (ev.which === 32) {
        ev.preventDefault();
        $radio.prop('checked', true);
      }
    });
  }
}
