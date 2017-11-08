/**
 * Helper methods to make it easy to access a widget's dom
 */
export class DOM {
  constructor({ document })  {
    this.props = { document };
  }

  /**
   * @param {Widget} widget
   * @return {null|Element}
   */
  findIframe = (widget) => {
    const { document } = this.props;

    const element = document.getElementById(widget.windowId);
    if (!element) { return null; }

    const iframe = element.querySelector('iframe');
    if (!iframe) { return null; }

    return iframe;
  };

  /**
   * @param {Widget} widget
   * @return {null|Window}
   */
  findWindow = (widget) => {
    const iframe = this.findIframe(widget);
    if (iframe) {
      return iframe.contentWindow;
    }
    return null;
  };
}
