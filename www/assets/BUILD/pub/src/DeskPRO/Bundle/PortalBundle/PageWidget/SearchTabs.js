import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class SearchTabs extends PageWidget {
  renderWidget() {
    this.$element.on('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      Array.prototype.forEach.call(document.getElementsByClassName('dp-po-search-sidebar-link active'), tab => tab.classList.remove('active'));
      Array.prototype.forEach.call(document.getElementsByClassName('dp-po-search-tabs-link active'), tab => tab.classList.remove('active'));
      const tab = e.target.dataset.type;
      e.target.classList.add('active');
      if (tab) {
        Array.prototype.forEach.call(document.getElementsByClassName('dp-po-search-details'), (t) => { t.style.display = 'none'; });
        Array.prototype.forEach.call(document.getElementsByClassName('dp-po-search-tab'), (t) => { t.style.display = 'none'; });
        Array.prototype.forEach.call(document.getElementsByClassName('dp-po-search-pager'), (t) => { t.style.display = 'none'; });
        const newTab = document.getElementById(`${tab}_tab`);
        if (newTab) {
          newTab.style.display = 'block';
        }
        const newPager = document.getElementById(`${tab}_pager`);
        if (newPager) {
          newPager.style.display = 'block';
        }
      }
    });
  }
}
