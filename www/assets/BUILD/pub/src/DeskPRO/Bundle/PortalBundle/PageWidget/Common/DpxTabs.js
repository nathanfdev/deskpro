import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class DpxTabs extends PageWidget {


  /**
   * The usage for this widget is quite simple:
   * <div>
   *   <ul>
   *     <li>
   *       <a href="#" class="dpx-tab" data-tab-id="tabContentId1">Tab 1</a>
   *     </li>
   *     <li>
   *       <a href="#" class="dpx-tab" data-tab-id="tabContentId2">Tab 2</a> <- here will be "active" class applied
   *     </li>
   *   </ul>
   *   <section id="tabContentId1" class="dpx-tab-content">
   *   </section>
   *   <section id="tabContentId2" class="dpx-tab-content">
   *   </section>
   */
  renderWidget() {
    const tabs       = $(this.$element).find('.dpx-tab');
    const containers = $(this.$element).find('.dpx-tab-content');
    tabs.on('click', (e) => {
      e.preventDefault();
      const clickedTab = $(e.target).hasClass('.dpx-tab') ? $(e.target) : $(e.target).closest('.dpx-tab');
      const tabId      = $(clickedTab).data('tabId');
      containers.each((containerIndex, container) => {
        let containerToShow;
        const $container = $(container);
        if ($container.attr('id') === tabId) {
          containerToShow = $container;
        } else {
          $container.hide();
        }
        if (containerToShow) {
          containerToShow.show();
        }

        tabs.each((tabIndex, tab) => {
          const $tab = $(tab);
          if ($tab.hasClass('active')) {
            $tab.removeClass('active');
          }
          clickedTab.addClass('active');
        });
      });
    });
    tabs[0].click();
  }
}
