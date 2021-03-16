import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';
import { HelpcenterCCDelete } from './HelpcenterCCDelete';

export class HelpcenterCCForm extends PageWidget {
  renderWidget() {
    const $form = this.$element;
    const $alert = $('.dp-po-ticket-meta-cc-form .alert');
    $form.on('submit', () => {
      $('#ticket-button-add-cc').prop('disabled', true);
      const $loading = $form.find('.dp-po-ticket-meta-cc-submit .dp-po-icon');
      $loading.show();
      $alert.hide();
      $form.find('.form-group small').hide();

      const list = $form.parents('.dp-po-ticket-meta-cc').find('ul.dp-po-ticket-meta-cc-list');

      $.ajax({
        type:    'POST',
        url:     $form.attr('action'),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data:    $form.serialize(),
        success(response) {
          $('#ticket-button-add-cc').prop('disabled', false);
          $loading.hide();
          if (response.data.success) {
            const html = response.data.html;
            list.append(html);
            $form.parent().hide();
            const classes = $(html).attr('class').replace(' ', '.');
            const w = new HelpcenterCCDelete(list.find(`.${classes} .dp-po-ticket-meta-cc-remove`));
            w.render();
            $form[0].reset();
            const count = document.querySelector('.dp-po-ticket-meta-cc .dp-po-ticket-meta-title .count');
            count.innerText = parseInt(count.innerText, 10) + 1;
          } else if (response.data.error) {
            $alert.html(response.data.error);
            $alert.show();
          } else if (response.data.errors) {
            $.each(response.data.errors, (key, value) => {
              $form.find(`.form-group.${key} small`).html(value).show();
            });
          }
        }
      }).done(() => {
        $('#ticket-button-add-cc').prop('disabled', false);
      });
      return false;
    });
  }
}
