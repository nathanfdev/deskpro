import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';

let id_incrementer = 1;

export class CustomPerFieldEdit extends PageWidget {

  renderWidget() {
    const $per_field = this.$element;
    const allow_edit = $per_field.data('allow-edit');
    const multiple = $per_field.data('multiple');
    const expanded = $per_field.data('expanded');
    let input_name = $per_field.data('input-name') + "[data]";
    const id = $per_field.data('id');
    const $choice_widget = $per_field.find('.deskpro-choice-widget');
    const type = 'per_user';

    if (!allow_edit) {
      // we only do this when the admin specifically allowed it for this field
      return;
    }

    const $addBtn = $('<a class="button button-plain button-small" href="#">New</a>');
    const $addInput = $('<input type="text" class="shorter" placeholder="Type here..." >');
    const $addSubmit = $('<a class="button button-plain button-small" href="#">Save</a>');
    const $addGroup = $('<div style="display: inline; width: 400px"></div>');
    $addInput.hide();
    $addSubmit.hide();
    $addGroup.append([$addBtn, $addInput, $addSubmit]);

    $addBtn.click((e) => {
      e.preventDefault();
      $addBtn.hide();
      $addInput.show();
      $addSubmit.show();
    });

    let markError = () => {
      $addInput.addClass('show-error');
      $addSubmit.show();
    };

    $addSubmit.click((e) => {
      e.preventDefault();
      $addSubmit.hide();
      portalHttp.sendPost(`/portal-data/custom-per/${type}/${id}`, { new_field: $addInput.val() }).
        then((r) => {
          if (r.isError()) {
            markError();
            return;
          }

          $addInput.hide();
          $addInput.val('');
          $addInput.removeClass('show-error');
          $addBtn.show();

          let response_choice = r.data.data.new;

          id_incrementer++;
          let new_id = 'dyanmic_id_new_custom_per'+(id_incrementer * 250);
          if (multiple) {
            input_name += "[]";
            if (expanded) { // CHECKBOX
              let $checkbox = $('<input type="checkbox" id="' + new_id + '" value="' + response_choice.id + '" name="' + input_name + '" /><label for="' + new_id + '">' + response_choice.title + '</label>');
              $choice_widget.prepend($checkbox);
              $('#' + new_id).prop('checked', true);
            } else { // MULTI-SELECT
              let $option = $('<option id="' + new_id + '" value="' + response_choice.id + '" name="' + input_name + '">' + response_choice.title + '</option>');
              $choice_widget.find('select').first().prepend($option);
              $('#' + new_id).prop('selected', true);
            }
          } else {
            if (expanded) { // RADIO
              let $checkbox = $('<input type="radio" id="' + new_id + '" value="' + response_choice.id + '" name="' + input_name + '" /><label for="' + new_id + '">' + response_choice.title + '</label>');
              $choice_widget.prepend($checkbox);
              $('#' + new_id).prop('checked', true);
            } else { // SELECT
              let $option = $('<option id="' + new_id + '" value="' + response_choice.id + '" name="' + input_name + '">' + response_choice.title + '</option>');
              $choice_widget.find('select').first().prepend($option);
              $('#' + new_id).prop('selected', true);
            }
          }


        }, (r) => {
          markError();
      });
    });

    $choice_widget.append($addGroup);
  }
}
