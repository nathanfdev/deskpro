import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

let counter = 1;

export class TitleAnchorWidget extends PageWidget {

  renderWidget() {
    const $element = $(this.$element);

    // ignore top bar
    if ($element.parents('.top-bar').length > 0) {
      return;
    }

    // ignore if there is already a link
    if ($element.parents('a').length > 0 || $element.children('a').length > 0) {
      return;
    }

    const text = $element[0].textContent;
    if (!text) {
      return;
    }

    const anchorId = `anchor-${counter}`;

    $element.attr('id', anchorId);
    $element.append($(`<a href="#${anchorId}" style="padding-left: 10px"><i class="fa fa-anchor title-anchor-icon" /></a>`));

    counter += 1;
  }
}
