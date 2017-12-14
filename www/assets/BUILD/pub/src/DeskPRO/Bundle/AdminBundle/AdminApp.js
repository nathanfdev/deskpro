import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import AppContainer from './Modules/Application/Components/AppContainer';

class AdminApp {

  static render(props, node) {
    ReactDOM.render(<AppContainer {...props} />, node);
  }

  static unmount(node) {
    ReactDOM.unmountComponentAtNode(node);
  }
}

export default AdminApp;
