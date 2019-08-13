import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

class CommunityTopicValueReader {

  constructor($formEl) {
    this.$formEl = $formEl;
  }

  parseIntSelect(f) { // eslint-disable-line class-methods-use-this
    return parseInt(f.val() || 0, 10) || 0;
  }

  getCategoryId() {
    return this.parseIntSelect(this.$formEl.find('#new_community_topic_channel'));
  }
}

export class CommunityTopicForm extends PageWidget {

  renderWidget() {
    const $expandedForm = this.$element.find('.community-topic-form-expanded');
    const $catSelect = this.$element.find('#new_community_topic_channel');
    const $communityTopicAttachments = this.$element.find('#new_feedback_more_attachments');
    const communityTopicReader = new CommunityTopicValueReader(this.$element);

    // detach the "Add More Attachments" button from the DOM (unnecessary if JS enabled)
    $communityTopicAttachments.remove();

    if ($expandedForm.data('do-show')) {
      $expandedForm.show();
    }

    this.processChangedCategory = () => {
      if (communityTopicReader.getCategoryId()) {
        $expandedForm.show();
        $catSelect.removeClass('error-large');
      } else {
        $catSelect.addClass('error-large');
        $expandedForm.hide();
      }
    };

    $catSelect.change(this.processChangedCategory);
  }
}
