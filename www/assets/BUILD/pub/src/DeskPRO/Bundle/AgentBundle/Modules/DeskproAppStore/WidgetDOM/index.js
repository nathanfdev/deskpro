import { ContainerDOM } from './ContainerDOM';
import { DOM } from './DOM';

export const widgetContainerAttribute = 'data-deskproapp';
export const WidgetDOM = DOM;

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
  const dom = new DOM({ document });
  return dom.findWindow(widget);
};
