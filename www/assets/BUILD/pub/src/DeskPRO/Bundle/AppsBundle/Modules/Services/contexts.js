import ReactDOM from 'react-dom';
import * as WidgetDOM from '../WidgetDOM';
import { Context } from '../Domain';
import { ContainerConfiguration } from './ContainerConfiguration';
import { renderAppsColumn, renderInPlace } from './renderers';

/**
 * @param {DeskPRO.Agent.PageFragment.Basic} page
 * @param {Location} location
 */
function extractContextPropsFromPage(page, location) {
  // extract page props
  const { TYPENAME: type } = page;
  const metadata = page.getMetaData(type);
  const pageProps = { type, entityId: metadata.id, pageId: page.pageUid };

  // extract tab props
  let tabProps = {};
  const tab = page.getTab();
  if (tab) {
    const tabUrlFragment = page.getMetaData('url_fragment');
    // ignore location.username and location.password since deskpro ain't using it
    const tabUrl = `${location.protocol}//${location.host}${location.pathname}#${tabUrlFragment}`;
    tabProps = { tabId: tab.id, tabUrl };
  }

  return { ...pageProps, ...tabProps };
}

/**
 * Returns a list of the properties of all contexts found in the page
 *
 * @param {DeskPRO.Agent.PageFragment.Basic} page
 * @param {Location} windowLocation
 * @return {Array<Object>}
 */
export function extractPageContextProps(page, windowLocation) {
  try {
    const foundNodes = WidgetDOM.container.findAllFromList(page.wrapper.get());
    if (foundNodes.length === 0) { return []; }

    // make sure all dom nodes have an id
    WidgetDOM.container.ensureContainerIds(foundNodes);
    // extract container props
    const attributeToProp = ({ id, 'data-deskproapp': locationId }) => ({ id, locationId });
    const containerPropsList = WidgetDOM.container.extractConfigurationFromList(foundNodes, attributeToProp);

    const pageProps = extractContextPropsFromPage(page, windowLocation);
    if (!pageProps) {
      // console.log('failed to extract container props from page', page);
      return [];
    }

    // build list of context objects
    return containerPropsList.map(containerProps => ({ ...pageProps, ...containerProps }));
  } catch (e) {
    // console.log('failed to extract app context from page fragment', e);
    return [];
  }
}

/**
 * @param {DeskPRO.Agent.PageFragment.Basic} page
 * @param {Location} windowLocation
 * @return Array<Context>
 */
function createContextsFromPage(page, windowLocation) {   // eslint-disable-line no-unused-vars
  return extractPageContextProps(page, windowLocation).map(Context.fromProps);
}

/**
 * @param {{page}} tab
 * @param {Location} windowLocation
 * @return Array<Context>
 */
function createContextsFromTab(tab, windowLocation) { // eslint-disable-line no-unused-vars
  return extractPageContextProps(tab.page, windowLocation).map(Context.fromProps);
}

/**
 * @param {Context} context
 * @param {Window} windowObject
 * @returns {ContainerConfiguration}
 */
export function readContextConfiguration(context, windowObject) { // eslint-disable-line no-unused-vars
  // find the dom node to mount at
  const domNode = WidgetDOM.container.findContainerById(context.id, windowObject.document);
  return ContainerConfiguration.fromDOM(domNode);
}

/**
 * @param {Context} context
 * @param {Window} windowObject
 */
export function unmountContextInWindow(context, windowObject) {
  // find the dom node to mount at
  const domNode = WidgetDOM.container.findContainerById(context.id, windowObject.document);
  if (ReactDOM.unmountComponentAtNode(domNode)) {
    console.info(`app container unmounted from location ${context.locationId}`);
  } else {
    console.warn(`failed to unmount app container from location ${context.locationId}`);
  }
}

/**
 * @param {Context} context
 * @param {Window} windowObject
 * @returns {function}
 */
export function mountContextStrategy(context, windowObject) {
  // find the dom node to mount at
  const mountAtNode = WidgetDOM.container.findContainerById(context.id, windowObject.document);
  const configuration = readContextConfiguration(context, windowObject);

  let renderer = null;

  const { renderType: renderStrategy } = configuration;
  if (renderStrategy === 'inplace') {
    renderer = renderInPlace;
  } else if (renderStrategy === 'apps-column') {
    renderer = renderAppsColumn;
  } else {
    throw new Error(`unknown render strategy: ${renderStrategy}`);
  }

  /**
   * @param {{}} store
   * @param {Context} context
   * @param {AppsConfig} config
   * @param {Array<WidgetConfiguration>} widgetsConfigList
   */
  function strategy({ store, context, config, widgetsConfigList })  { // eslint-disable-line no-shadow
    if (widgetsConfigList.length && ['ticket-sidebar', 'person-sidebar', 'org-sidebar'].indexOf(context.locationId) > -1) {
      const tab = windowObject.DeskPRO_Window.TabBar.getTab(context.tabId);
      if (tab && tab.page) {
        tab.page.updateAppsSidebar();
      }
    }

    ReactDOM.render(renderer({ store, context, config, widgetsConfigList }), mountAtNode);
  }

  return strategy;
}
