import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export default class ClickAwayDropdownWidget extends PageWidget {
  renderWidget() {
    const $trigger = this.$element;
    const targetId = $trigger.data('clickaway-target');
    const $target = $(`#${targetId}`);

    $trigger.click((event) => {
      event.preventDefault();
      event.stopPropagation();
      $target.toggle();
      if ($target.is(':visible')) {
        $target.find('input:visible').first().focus();
      }
    });

    $target.click((event) => {
      event.stopPropagation();
    });

    $(document).click(() => {
      $target.hide();
    });
  }
}
