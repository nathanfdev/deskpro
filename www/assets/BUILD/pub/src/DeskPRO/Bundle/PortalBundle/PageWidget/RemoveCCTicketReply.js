import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class RemoveCCTicketReply extends PageWidget {
  renderWidget() {
    this.$element.on('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const participantId = this.$element.data('participant-id');
      const form = $('#ticket-reply-form');
      const ccRemove = form.find('#ticket_reply_cc_remove');
      if (ccRemove.length === 0) {
        $(`<input type="hidden" name="ticket_reply[cc_remove]" id="cc-remove" value="${participantId}" />`).appendTo(form);
      } else {
        const ccs = ccRemove.val().split(',');
        ccs.push(participantId);
        ccRemove.val(ccs);
      }
      if ($('.dp-po-ticket-block-cc-item').length === 2) {
        this.$element.parents('.dp-po-ticket-block-cc').remove();
      } else {
        this.$element.parents('.dp-po-ticket-block-cc-item').remove();
      }
    });
  }
}
