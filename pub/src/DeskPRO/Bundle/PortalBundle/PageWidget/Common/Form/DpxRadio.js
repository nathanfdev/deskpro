import $ from "jquery";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";

export default class DpxRadio extends PageWidget {
  renderWidget() {
    const $el = this.$element;
    const $radio = $el.find('input');

    $el.attr('tabindex', '0');

    $el.on('keydown', (ev) => {
      if (ev.which === 32) {
        ev.preventDefault();
        $radio.prop('checked', true);
      }
    });
  }
}
