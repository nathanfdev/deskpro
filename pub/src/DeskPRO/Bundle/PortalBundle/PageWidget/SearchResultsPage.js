import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import { portalUrlGenerator } from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator";
import $ from "jquery"
import _ from "lodash"

class SearchResultCollection {
  constructor() {
    this.collection = [];
  }

  add(search_results) {
    this.collection.push(search_results);
  }
}

class DynamicSearchResults {
  constructor($ul, $btn, query, type) {
    this.next_page = 1;
    this.query = query;
    this.type = type;
    this.$ul = $ul;
    this.$btn = $btn;
    this.is_next = $ul.data('next');

    $btn.click((e) => {
        e.preventDefault();
        this.loadNextPage();
    });

    this.toggleNextBtn();
  }

  loadNextPage() {
    const url = portalUrlGenerator.path(`/search?q=${this.query}&page=${this.next_page}&type=${this.type}`);

    $.ajax(url, {
        success: (data) => {
          this.is_next = $(data).find('.dpx-search-result-section').data('next') == '1';
          this.appendResults(data);
          this.next_page++;
          this.toggleNextBtn();
        }
    });
  }

  toggleNextBtn() {
    if (this.is_next) {
      this.$btn.show();
    } else {
      this.$btn.hide();
    }
  }

  appendResults(data) {
      const $show_more = this.$ul.find('.dpx-show-more');
      // the first time we request a new page, we have to remove the initial page-loaded results because
      // the new requests will repeat that data (per_page is higher)
      if (this.next_page == 1) {
        this.$ul.find('li').not($show_more).hide();
      }
      $show_more.before(data);
  }
}

export default class SearchResultsPage extends PageWidget {
  renderWidget() {
    const $search_results_page = this.$element;
    const collection = new SearchResultCollection();

    $search_results_page.find('ul.dpx-dynamic-search-results').each(function() {
      const $ul = $(this);
      collection.add(
          new DynamicSearchResults(
              $ul,
              $ul.find('.dpx-show-more a'),
              $search_results_page.data('query'),
              $ul.data('type')
          )
      )
    });
  }
}
