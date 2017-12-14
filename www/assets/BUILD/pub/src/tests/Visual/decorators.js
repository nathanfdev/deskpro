import React from 'react';
import { IntlProvider } from 'react-intl';
import { fakeState } from 'Helpers';
import { Provider } from 'react-redux';
import { createStore } from 'redux';
import $ from 'jquery';

// If the requested URL matches storybook server URL, then using the default webpack URL to load assets
// and make an AJAX request to load the assets URL otherwise (in case of browsing a static storybook dump)
let assetsUrl = window.location.href.startsWith('http://localhost:9001/')
  ? 'http://localhost:9666/pub/build'
  : false;

if (!assetsUrl) {
  $.ajax({
    url:   '/sys/ui/assets-path',
    success(path) { assetsUrl = path; },
    async: false
  });
}

export function css(jsx) {
  return (
    <div>
      <link type="text/css" rel="stylesheet" href={`${assetsUrl}/DeskPRO_AgentBundle_style.css`} />
      <link type="text/css" rel="stylesheet" href={`${assetsUrl}/DeskPRO_AgentLegacyBundle_style.css`} />
      {jsx}
    </div>
  );
}

export function demoCss(jsx) {
  return (
    <div>
      <link type="text/css" rel="stylesheet" href={`${assetsUrl}/DeskPRO_DemoBundle_style.css`} />
      {jsx}
    </div>
  );
}

export function adminCss(jsx) {
  return (
    <div>
      <link type="text/css" rel="stylesheet" href={`${assetsUrl}/DeskPRO_AdminBundle_style.css`} />
      {jsx}
    </div>
  );
}

export function reportCss(jsx) {
  return (
    <div>
      <link type="text/css" rel="stylesheet" href={`${assetsUrl}/DeskPRO_ReportBundle_style.css`} />
      {jsx}
    </div>
  );
}

export function redux(state, jsx) {
  const store = createStore(s => s, fakeState(state));

  return (
    <Provider store={store}>
      <IntlProvider locale="en">
        {jsx}
      </IntlProvider>
    </Provider>
  );
}

window.DESKPRO_APP_ASSETS_URL = assetsUrl;
export const appAssetsUrl = assetsUrl;
