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
 *  If this browser is able to use the RTE, then we copy the HTML
 *  from the html field into the textarea, then add the RTE on that
 *  textarea, then switch the format to 'html'.
 *
 *  On the back-end, we know how to treat the textarea based on the 'format'.
 *  The value of the html field is now ignored, because the textarea will always
 *  be the content we need to process.
 */
export default class DpxRte extends PageWidget {
  renderWidget() {
    const $txt     = this.$element.find('textarea[data-rte-field="text"]');
    const $html    = this.$element.find('input[data-rte-field="html"]');
    const $format  = this.$element.find('input[data-rte-field="format"]');

    $txt.val($html.val());
    $txt.wrap('<div class="dp-medium-rte-wrapper as-dpui" />');

    const $wrap = $txt.parent();

    $txt.addClass('dp-medium-rte');
    const editor = new MediumEditor($txt.get(0), {
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
