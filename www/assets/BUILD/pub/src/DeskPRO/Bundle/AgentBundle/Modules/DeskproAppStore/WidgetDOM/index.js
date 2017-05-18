import { ContainerDOM } from './ContainerDOM';

export const widgetContainerAttribute = 'data-deskproapp';

/**
 * @type {ContainerDOM}
 */
export const container = ContainerDOM.fromAttributeName(widgetContainerAttribute);

/**
 * @param {Widget} widget
 * @param document
 * @return {null|Window}
 */
export const findWidgetWindow = (widget, document) => {

  const element = document.getElementById(widget.windowId);
  if (! element) { return null; }

  const iframe = element.querySelector('iframe');
  if (! iframe ) { return null; }

  return iframe.contentWindow;
};
