import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import $ from "jquery"

//######################################################################################################################
//# Feedback value reader
//######################################################################################################################

class FeedbackValueReader {
  constructor($formEl) {
    this.$formEl = $formEl;
  }

  _parseIntSelect(f) {
    return parseInt(f.val() || 0) || 0;
  }

  getCategoryId() {
    return this._parseIntSelect(this.$formEl.find('#new_feedback_category'));
  }
}

//######################################################################################################################
//# Feedback form widget
//######################################################################################################################

export default class FeedbackForm extends PageWidget {
  renderWidget() {
    let $formEl = this.$element.find('.feedback-form-interactive');
    let $expandedForm = this.$element.find('.feedback-form-expanded');
    let $startBtn = this.$element.find('.feedback-selected-start');
    let $catSelect = this.$element.find('#new_feedback_category');
    let $feedbackAttachments = this.$element.find('#new_feedback_more_attachments');
    let feedbackReader = new FeedbackValueReader(this.$element);

    // deatch the "Add More Attachments" button from the DOM (unnecessary if JS enabled)
    $feedbackAttachments.remove();

    if ($expandedForm.data('do-show')) {
      $expandedForm.show();
    }

    this.processChangedCategory = () => {
      if (feedbackReader.getCategoryId()) {
        $expandedForm.show();
        $catSelect.removeClass('error-large');
      } else {
        $catSelect.addClass('error-large');
        $expandedForm.hide();
      }
    };

    $catSelect.change(this.processChangedCategory);

    $startBtn.on('click', (e) => {
      e.preventDefault();
      this.processChangedCategory();
    })
  }
}
