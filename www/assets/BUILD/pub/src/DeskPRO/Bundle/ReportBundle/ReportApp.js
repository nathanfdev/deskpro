import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import '../../../../vendor/amcharts3/amcharts';
import '../../../../vendor/amcharts3/serial';
import '../../../../vendor/amcharts3/pie';
import '../../../../vendor/amcharts3/themes/light';
import AppContainer from './Modules/Application/Components/AppContainer';

class ReportApp {

  static render(props, node) {
    ReactDOM.render(<AppContainer {...props} />, node);
  }
}

export default ReportApp;
