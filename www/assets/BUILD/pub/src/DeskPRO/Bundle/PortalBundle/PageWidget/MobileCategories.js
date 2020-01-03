import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class MobileCategories extends PageWidget {
  renderWidget() {
    this.$element.on('click', function () {
      $(this).toggleClass('active');
      $(this).parents('.dp-po-category-title').next('.dp-po-kb-category-list').slideToggle();
      $(this).parents('.dp-po-category-title').siblings('.dp-po-viewall').toggleClass('active');
      return false;
    });
  }
}
