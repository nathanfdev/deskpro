import 'babel-polyfill';
import 'amcharts3/amcharts/amcharts';
import 'amcharts3/amcharts/serial';
import 'amcharts3/amcharts/pie';
import React from 'react';
import ReactDOM from 'react-dom';
import AppContainer from './Modules/Application/Components/AppContainer';

class ReportApp {

  static render(props, node) {
    ReactDOM.render(<AppContainer {...props} />, node);
  }
}

export default ReportApp;
