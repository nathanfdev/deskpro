import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import FeedbackFilter from "DeskPRO/Bundle/PortalBundle/PageWidget/FeedbackFilter"
import FeedbackForm from "DeskPRO/Bundle/PortalBundle/PageWidget/FeedbackForm"

export default class FeedbackPage extends PageWidget {
  init() {
    this.addWidgetDef(FeedbackFilter, ".feedback-filter-interactive");
    this.addWidgetDef(FeedbackForm, ".feedback-form-interactive");
  }
  renderWidget() {
  }
}
