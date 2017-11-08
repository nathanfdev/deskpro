import * as WidgetDOM from '../WidgetDOM';
import { Context } from '../Domain';

/**
 * @param {DeskPRO.Agent.PageFragment.Basic} page
 * @param {Location} location
 */
const extractContextPropsFromPage = (page, location) => {
  // extract page props
  const { TYPENAME: type } = page;
  const metadata = page.getMetaData(type);
  const pageProps = { type, entityId: metadata.id };

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
};

/**
 * Returns a list of the properties of all contexts found in the page
 *
 * @param {DeskPRO.Agent.PageFragment.Basic} page
 * @param {Location} windowLocation
 * @return {Array<Object>}
 */
export const extractPageContextProps = (page, windowLocation) => {
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
};

/**
 * @param {DeskPRO.Agent.PageFragment.Basic} page
 * @param {Location} windowLocation
 * @return Array<Context>
 */
export const createContextsFromPage = (page, windowLocation) => extractPageContextProps(page, windowLocation).map(Context.fromProps);

/**
 * @param {{page}} tab
 * @param {Location} windowLocation
 * @return Array<Context>
 */
export const createContextsFromTab = (tab, windowLocation) => createContextsFromPage(tab.page, windowLocation);

/**
 * @param {Context} context
 * @param {ContainerMounter} containerMounter
 * @param {Window} windowObject
 */
export const unmountContextInWindow = (context, containerMounter, windowObject) => {
  // find the dom node to mount at
  const mountAtNode = WidgetDOM.container.findContainerById(context.id, windowObject.document);
  containerMounter.unmountAt(context, mountAtNode);
};

/**
 * @param {Context} context
 * @param {ContainerMounter} containerMounter
 * @param {Window} windowObject
 */
export const mountContextInWindow = (context, containerMounter, windowObject) => {
  // find the dom node to mount at
  const mountAtNode = WidgetDOM.container.findContainerById(context.id, windowObject.document);

  // TODO have the context initiate any post-mounting activities. This is quick hack-fix
  const nrOfWidgets = containerMounter.mountAt(context, mountAtNode);
  if (nrOfWidgets && ['ticket-sidebar', 'person-sidebar', 'org-sidebar'].indexOf(context.locationId) > -1) {
    const tab = windowObject.DeskPRO_Window.TabBar.getTab(context.tabId);
    tab.page.updateAppsSidebar();
  }
};
