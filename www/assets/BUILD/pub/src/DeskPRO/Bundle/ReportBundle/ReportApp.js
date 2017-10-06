import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import AppContainer from './Modules/Application/Components/AppContainer';

class ReportApp {

  static render(props, node) {
    ReactDOM.render(<AppContainer {...props} />, node);
  }
}

export default ReportApp;
