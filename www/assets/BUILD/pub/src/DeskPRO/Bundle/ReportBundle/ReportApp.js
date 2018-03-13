import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { AppContainer } from 'react-hot-loader';
import $ from 'jquery';
import DataTables from 'datatables.net';
import '../../../../vendor/datatables/plugins/pageResize';

import '../../../../vendor/amcharts3/amcharts';
import '../../../../vendor/amcharts3/serial';
import '../../../../vendor/amcharts3/pie';
import '../../../../vendor/amcharts3/gauge';
import '../../../../vendor/amcharts3/gantt';
import '../../../../vendor/amcharts3/funnel';
import '../../../../vendor/amcharts3/radar';
import '../../../../vendor/amcharts3/xy';
import '../../../../vendor/amcharts3/themes/light';

import App from './Modules/Application/Components/AppContainer';

$.DataTable = DataTables;

class ReportApp {

  static render(props, node) {
    ReactDOM.render(<AppContainer><App {...props} /></AppContainer>, node);
  }
}

if (module.hot) {
  module.hot.accept();
}

export default ReportApp;
