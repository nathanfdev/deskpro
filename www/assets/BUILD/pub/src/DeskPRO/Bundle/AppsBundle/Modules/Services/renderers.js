import React from 'react';
import { Provider } from 'react-redux';

import { DeskproAppContainer, AppsColumnContainer, WidgetListProvider } from '../Components';
import getSidebarState from './sidebarState';
import { AppsConfig } from '../Config'; // eslint-disable-line no-unused-vars

/**
 * @return {DeskPRO.MessageBroker}
 */
function sendMessageLegacyMessageBroker(name, data) {
  return window.DeskPRO_Window.getMessageBroker().sendMessage(name, data);
}

/**
 * @param {{}} store
 * @param {Context} context
 * @param {Array<WidgetConfiguration>} widgetsConfigList
 */
export function renderInPlace({ store, context, widgetsConfigList }) { // eslint-disable-line react/prop-types
  // eslint-disable-line react/prop-types
  return (
    <Provider store={store}>
      <WidgetListProvider widgetsConfigList={widgetsConfigList}>
        {nextWidgetsConfigList => (<DeskproAppContainer widgetsConfigList={nextWidgetsConfigList} context={context} />)}
      </WidgetListProvider>
    </Provider>
  );
}

/**
 * @param {{}} store
 * @param {Context} context
 * @param {Array<WidgetConfiguration>} widgetsConfigList
 */
export function renderAppsColumn({ store, context, widgetsConfigList }) { // eslint-disable-line react/prop-types
  // eslint-disable-line react/prop-types
  return (
    <Provider store={store}>
      <WidgetListProvider widgetsConfigList={widgetsConfigList}>
        {
          nextWidgetsConfigList => (
            <AppsColumnContainer
              context={context}
              widgetsConfigList={nextWidgetsConfigList}
              getSidebarState={getSidebarState}
              sendMessageLegacyMessageBroker={sendMessageLegacyMessageBroker}
            />
        )}
      </WidgetListProvider>

    </Provider>
  );
}
