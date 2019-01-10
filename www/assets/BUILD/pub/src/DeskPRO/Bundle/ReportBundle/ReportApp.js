import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { AppContainer } from 'react-hot-loader';
import $ from 'jquery';
import DataTables from 'datatables.net';
import '../../../../vendor/datatables/plugins/pageResize';
import '../../../../vendor/datatables/plugins/dataTables.rowsGroup';

import '../../../../vendor/amcharts3/amcharts';
import '../../../../vendor/amcharts3/serial';
import '../../../../vendor/amcharts3/pie';
import '../../../../vendor/amcharts3/gauge';
import '../../../../vendor/amcharts3/gantt';
import '../../../../vendor/amcharts3/funnel';
import '../../../../vendor/amcharts3/radar';
import '../../../../vendor/amcharts3/xy';
import '../../../../vendor/amcharts3/themes/light';
import '../../../../vendor/amcharts3/plugins/export/export';

import App from './Modules/Application/Components/AppContainer';

$.DataTable = DataTables;

window.initHandlebars = (Handlebars) => {
  if (Handlebars.dpHasDoneInit) {
    return;
  }
  Handlebars.dpHasDoneInit = true;
  Handlebars.registerHelper('formatNumber', (value, info = {}) => {
    const numValue = Number(value);
    if (isNaN(numValue)) {
      return value;
    }
    try {
      return numValue.toLocaleString('en-US', info.hash || {});
    } catch (e) {
      return value;
    }
  });

  Handlebars.registerHelper('formatCurrency', (value, currency, info = {}) => {
    const numValue = Number(value);
    if (isNaN(numValue)) {
      return value;
    }
    try {
      const options = info.hash || {};
      options.style = 'currency';
      options.currency = currency;
      return numValue.toLocaleString('en-US', options);
    } catch (e) {
      return value;
    }
  });

  Handlebars.registerHelper('formatPercent', (value) => {
    const numValue = Number(value);
    if (isNaN(numValue)) {
      return value;
    }

    return `${parseInt(value, 10)}%`;
  });

  Handlebars.registerHelper('math', (lvalue, operator, rvalue) => {
    const lval = parseFloat(lvalue);
    const rval = parseFloat(rvalue);

    return {
      '+': lval + rval,
      '-': lval - rval,
      '*': lval * rval,
      '/': lval / rval,
      '%': lval % rval
    }[operator];
  });
};

class ReportApp {

  static render(props, node) {
    ReactDOM.render(<AppContainer><App {...props} /></AppContainer>, node);
  }
}

if (module.hot) {
  module.hot.accept();
}

export default ReportApp;
