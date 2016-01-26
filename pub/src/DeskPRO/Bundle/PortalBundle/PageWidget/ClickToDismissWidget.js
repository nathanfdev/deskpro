import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';

export default class ClickToDismissWidget extends PageWidget {

  renderWidget() {
    const link = this.$element.data('click-to-dismiss');

    this.$element.click((e) => {
      e.preventDefault(); // the element this is attached to usually is NOT an <a> tag, but just in case

      if (link) {
        // we have a redirect link that will dismiss this alert for this session, send them there
        window.location.href = link;
      } else {
        // no data-click-to-dismiss link, we should find the parent and hide it to hide the alert
        this.$element.closest('.alert').hide();
      }
    });
  }
}
