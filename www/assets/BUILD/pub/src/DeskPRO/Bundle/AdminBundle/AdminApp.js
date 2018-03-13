import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { AppContainer } from 'react-hot-loader';
import App from './Modules/Application/Components/AppContainer';

class AdminApp {

  static render(props, node) {
    ReactDOM.render(<AppContainer><App {...props} /></AppContainer>, node);
  }

  static unmount(node) {
    ReactDOM.unmountComponentAtNode(node);
  }
}

if (module.hot) {
  module.hot.accept();
}

export default AdminApp;
