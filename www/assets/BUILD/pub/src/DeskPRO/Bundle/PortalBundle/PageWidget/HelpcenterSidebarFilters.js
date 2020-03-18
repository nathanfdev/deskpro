import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class HelpcenterSidebarFilters extends PageWidget {

  renderWidget() {
    const $form = this.$element;

    $form.find('.dpx-clear-all').on('click', (event) => {
      event.preventDefault();
      $form.find('input[type=checkbox]').prop('checked', false).trigger('change');
    });

    $form.find('.dpx-group-checkbox').each((i, groupCheckbox) => {
      const $groupCheckbox = $(groupCheckbox);
      const $filterGroup = $groupCheckbox.parents('.dpx-filter-group');
      const $checkboxes = $filterGroup.find('input[type=checkbox]:not(.dpx-group-checkbox)');
      const hasChecked = !!$checkboxes.filter((k, checkbox) => $(checkbox).is(':checked')).length;

      $groupCheckbox.prop('checked', hasChecked).trigger('change');
    });
    $form.find('.dpx-group-checkbox').on('click', (event) => {
      const $groupCheckbox = $(event.currentTarget);
      const $filterGroup = $groupCheckbox.parents('.dpx-filter-group');
      const $checkboxes = $filterGroup.find('input[type=checkbox]:not(.dpx-group-checkbox)');
      const hasUnchecked = !!$checkboxes.filter((i, checkbox) => !$(checkbox).is(':checked')).length;

      if (hasUnchecked) {
        $groupCheckbox.prop('checked', true).trigger('change');
        $checkboxes.prop('checked', true).trigger('change');
      } else {
        $checkboxes.prop('checked', $groupCheckbox.is(':checked')).trigger('change');
      }
    });

    $form.find('input[type=checkbox]:not(.dpx-group-checkbox)').on('click', () => {
      const $checkbox = $(event.currentTarget);
      const $groupCheckbox = $checkbox.parents('.dpx-filter-group').find('.dpx-group-checkbox');
      const $checkboxes = $checkbox.parents('.dpx-filter-group').find('input[type=checkbox]:not(.dpx-group-checkbox)');
      const hasChecked = !!$checkboxes.filter((i, checkbox) => $(checkbox).is(':checked')).length;

      $groupCheckbox.prop('checked', hasChecked).trigger('change');
    });
  }
}
