import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class HtmlLinkToPostWidget extends PageWidget {

  renderWidget() {
    const $postLinks = this.$element.find('a.post-link');

    $postLinks.each(function () {
      const $link = $(this);

      $link.click((e) => {
        e.preventDefault();

        $postLinks.each(function () {
          $(this).addClass('isDisabled');
        });

        const action = $link.attr('href');
        const $form = $('<form></form>');
        const $input = $('<input name="_dp_csrf_token" />').val(window.dp_get_csrf_token());

        $form.attr('action', action);
        $form.attr('method', 'POST');
        $form.append($input);
        $('body').append($form);
        $form.submit();

        return false;
      });
    });
  }
}
