import $ from "jquery";
import MediumEditor from "medium-editor";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";

/**
 * A DpxRte takes three fields:
 *
 *  textarea -- the main form element
 *  html     -- a hidden form element containing HTML code
 *  format   -- a hidden form element containing the edit mode we're in (text/html)
 *
 *  If this browser is able to use the RTE, then we hide the txt field,
 *  show the html field, and set the format to html
 */
export default class DpxRte extends PageWidget {
  renderWidget() {
    const $textTextarea = this.$element.find('textarea[data-rte-field="text"]');
    const $htmlTextarea = this.$element.find('textarea[data-rte-field="html"]');
    const $format       = this.$element.find('input[data-rte-field="format"]');

    $htmlTextarea.wrap('<div class="dp-medium-rte-wrapper as-dpui" />');
    const $wrap = $htmlTextarea.parent();

    $htmlTextarea.addClass('dp-medium-rte');
    $htmlTextarea.show();
    $textTextarea.hide();

    const editor = new MediumEditor($htmlTextarea.get(0), {
      toolbar: {
        buttons: ['bold', 'italic', 'underline', 'anchor', 'unorderedlist', 'orderedlist', 'quote', 'pre', 'removeFormat'],
        static: true,
        sticky: true,
        updateOnEmptySelection: true,
        align: 'left',
        relativeContainer: $wrap.get(0)
      },
      targetBlank: true,
      buttonLabels: 'fontawesome'
    });

    $format.val('html');
  }
}
