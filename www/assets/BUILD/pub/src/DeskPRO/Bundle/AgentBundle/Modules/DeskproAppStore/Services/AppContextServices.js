import * as WidgetDOM from '../WidgetDOM';
import { Context } from '../Domain';

/**
 * @param {DeskPRO.Agent.PageFragment.Basic} page
 * @param {Location} location
 */
const extractPropsFromPage = (page, location) => {
  const tab = page.getTab();
  if (!tab) { return null; }

  // extract tab props
  const { TYPENAME: type } = page;
  const metadata = page.getMetaData(type);
  const tabUrlFragment = page.getMetaData('url_fragment');

  // ignore location.username and location.password since deskpro ain't using it
  const tabUrl = `${location.protocol}//${location.host}${location.pathname}#${tabUrlFragment}`;

  return { type, entityId: metadata.id, tabId: tab.id, tabUrl };
};

/**
 * @param {Object} tab
 * @param {Location} windowLocation
 * @return Array<Context>
 */
export const createContextsFromTab = (tab, windowLocation) => this.createContextsFromPage(tab.page, windowLocation);

/**
 * @param {DeskPRO.Agent.PageFragment.Basic} page
 * @param {Location} windowLocation
 * @return Array<Context>
 */
export const createContextsFromPage = (page, windowLocation) => {
  try {
    const foundNodes = WidgetDOM.container.findAllFromList(page.wrapper.get());
    if (foundNodes.length === 0) { return []; }

    // make sure all dom nodes have an id
    WidgetDOM.container.ensureContainerIds(foundNodes);
    // extract container props
    const attributeToProp = ({ id, 'data-deskproapp': locationId }) => ({ id, locationId });
    const containerPropsList = WidgetDOM.container.extractConfigurationFromList(foundNodes, attributeToProp);

    const pageProps = extractPropsFromPage(page, windowLocation);
    if (!pageProps) {
      console.log('failed to extract container props from page', page);
      return [];
    }

    // build list of context objects
    return containerPropsList
      .map(containerProps => ({ ...pageProps, ...containerProps }))
      .map(Context.fromProps)
      ;
  } catch (e) {
    console.log('failed to extract app context from page fragment', e);
    return [];
  }
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
