import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class HtmlLinkToPostWidget extends PageWidget {

  renderWidget() {
    const $postLinks = this.$element.find('a.post-link');

    $postLinks.each(function () {
      const $link = $(this);

      $link.click((e) => {
        e.preventDefault();

        const action = $link.attr('href');
        const $form = $('<form></form>');

        $form.attr('action', action);
        $form.attr('method', 'POST');
        $('body').append($form);
        $form.submit();

        return false;
      });
    });
  }
}
