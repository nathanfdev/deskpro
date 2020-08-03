import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { copyTextToClipboard } from 'DeskPRO/Component/Util/ClipBoard';
import $ from 'jquery';
import { portalPhrases } from '../PortalPhrases';

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

    // Also ignore guides
    if ($element.parents('.dp-po-guides-block-content').length > 0) {
      return;
    }


    const text = $element[0].textContent;
    if (!text) {
      return;
    }

    const anchorId = `anchor-${counter}`;

    $element.attr('id', anchorId);
    const a = window.document.createElement('a');
    a.setAttribute('href', `#${anchorId}`);
    a.classList.add('dp-po-clipboard-link');
    a.setAttribute('style', 'padding-left: 10px');

    const span = window.document.createElement('span');
    span.innerText = portalPhrases.get('helpcenter.general.copied');

    const copyToClipbard = (e) => {
      e.preventDefault();
      if (copyTextToClipboard(`${window.location.href}#${anchorId}`)) {
        a.append(span);
        setTimeout(() => {
          a.removeChild(span);
        }, 1000);
      }
      return false;
    };

    a.addEventListener('click', copyToClipbard);
    const i = window.document.createElement('i');
    i.classList.add('fa', 'fa-anchor', 'title-anchor-icon');
    i.setAttribute('title', `Copy link to ${text} to clipboard`);
    a.append(i);
    $element.append(a);

    counter += 1;
  }
}
