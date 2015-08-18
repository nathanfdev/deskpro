import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import FeedbackPagedResults from "DeskPRO/Bundle/PortalBundle/PageWidget/FeedbackPagedResults"
import $ from "jquery"

class FilterControls {
  // this will be a model of the filter "form" (its a DOM piece with <a> elements that link for the
  // non-js users to have limited functionality.
  // should non-js users just have a "as-no-dpui" for the filter and never deal with them so we can
  // always draw it ourselves? react with a PHP generated "init" data model?
}

export default class FeedbackFilter extends PageWidget {
  init() {
    this.addWidgetDef(FeedbackPagedResults, '.paged-results')
  }
  replacePagedResultsWidget(newHtml) {
    this.$element.find('.paged-results').replaceWith('<div class="paged-results">' + newHtml + '</div>');
    this.addWidgetDef(FeedbackPagedResults, '.paged-results');
  }
  fetchUrlAndUpdateUi(url) {
    $.get(url, (data)  => {
      console.log('updating with data from: ' + url);
      this.replacePagedResultsWidget(data);
      this.renderWidget();
    });
  }
  renderWidget() {
    this.$element.find('a').each((i, a) => {
      $(a).on('click', (e) => {
        e.preventDefault();
        let url = $(a).attr('href');
        if (url) {
          this.fetchUrlAndUpdateUi(url);
        }
      });
    });
  }
}
