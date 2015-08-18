import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import $ from "jquery"

export default class FeedbackPagedResults extends PageWidget {
  renderWidget() {
    // it is entirely possible that this class (Widget) will serve absolitely no purpose
    // but we MIGHT need to use it for pagination (the pagination links are part of the
    // "filter" but must be updated from the server when we fetch new data).
    // right now its just A links, and that's fine, but we still need to attach events
    // that the parent needs to respond to.
  }
}
