import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import $ from 'jquery';

class SearchResultCollection {

  constructor() {
    this.collection = [];
  }

  add(searchResults) {
    this.collection.push(searchResults);
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
        this.is_next = $(data).find('.dpx-search-result-section').data('next') === '1';
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
    const $showMore = this.$ul.find('.dpx-show-more');
    // the first time we request a new page, we have to remove the initial page-loaded results because
    // the new requests will repeat that data (per_page is higher)
    if (this.next_page === 1) {
      this.$ul.find('li').not($showMore).hide();
    }

    $showMore.before(data);
  }
}

export class SearchResultsPage extends PageWidget {

  renderWidget() {
    const $searchResultsPage = this.$element;
    const collection = new SearchResultCollection();

    $searchResultsPage.find('ul.dpx-dynamic-search-results').each(function() {
      const $ul = $(this);
      collection.add(new DynamicSearchResults(
        $ul,
        $ul.find('.dpx-show-more a'),
        $searchResultsPage.data('query'),
        $ul.data('type')
      ));
    });
  }
}
