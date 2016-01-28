import { PageWidget } from './PageWidget';

/**
 * A PageWidgetGroup is exactly like a PageWidget except error handling is done a bit differently.
 *
 * In a PageWidget, if the render() fails, children widgets (via renderWidgets) are still called.
 * This is ideal for PageWidgets that are basically just configs (e.g., adds nice custom form widgets).
 * However, this is bad for PageWidgets that are part of a group where the children widgets rely on the parent.
 *
 * So if you have a group of widgets where the children widgets require the parent to work,
 * you should use PageWidgetGroup. This ensures that in an error case, the children dont get instantiated.
 * And hopefully you've added a nice fallback for non-JS users that 'just works'.
 */
export default class PageWidgetGroup extends PageWidget {

  /**
   * Called to a request to render itself.
   */
  render() {
    const pre = this.preRender();

    if (pre && pre.then) {
      pre.then(() => {
        this.renderWidget();
        this.runWidgets();
      });
    } else {
      this.renderWidget();
      this.runWidgets();
    }
  }
}
