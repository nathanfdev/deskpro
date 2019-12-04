import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { AppContainer } from 'react-hot-loader';
import { addLocaleData, IntlProvider } from 'react-intl';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AdminBundle/DAL/config';
import App from './Modules/Application/Components/AppContainer';
import IconPicker from '../AgentBundle/Modules/Publish/Components/Content/IconPicker';
import store from './Services/store';
import { loadAdminPhraseTranslations } from './Modules/Application/Actions/bootstrapActions';

class AdminApp {

  static render(props, node) {
    ReactDOM.render(<AppContainer><App {...props} /></AppContainer>, node);
  }

  static unmount(node) {
    ReactDOM.unmountComponentAtNode(node);
  }

  static renderIconPicker(node, icon) {
    ReactDOM.render(
      <AppContainer>
        <Provider store={store}>
          <IntlProvider
            locale={window.DP_LOCALE.replace(/_/, '-')}
            messages={agentPhrases.getPhrases()}
          >
            <IconPicker
              icon={icon}
            />
          </IntlProvider>
        </Provider>
      </AppContainer>,
      node.get(0)
    );
  }

  run() {
    const possibleLocale = window.DP_LOCALE.replace(/-/, '_').split(/_/)[0] || 'en';
    try {
      addLocaleData(require(`react-intl/locale-data/${possibleLocale}`)); // eslint-disable-line import/no-dynamic-require, global-require
    } catch (e) {
      addLocaleData(require('react-intl/locale-data/en')); // eslint-disable-line import/no-dynamic-require, global-require
    }

    // Bootstrap API and DAL
    setApi(api);
    loadRepositoriesConfig(repositoriesConfig);

    store.dispatch(loadAdminPhraseTranslations());
  }
}

if (module.hot) {
  module.hot.accept();
}

export default AdminApp;
