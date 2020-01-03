import './publicPath';
import 'react-hot-loader/patch';
import { helpcenterApp } from './HelpcenterApp';
import $ from 'jquery';
import { addLocaleData } from 'react-intl';

import('./hc-lazy-inc').then(function() {
  $(document).ready(function() {
    const toolOptions = {
      template: '<div class="tooltip dp-po-tip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
    };

    $('[data-toggle="tooltip"]').tooltip(toolOptions);
    $('[data-toggle="popover"]').popover();
    $('.dropdown-toggle').dropdown();
  });
});

const possibleLocale = window.DESKPRO_LOCALE.replace(/-/, '_').split(/_/)[0] || 'en';

import(
  /* webpackPreload: true */
  `react-intl/locale-data/${possibleLocale}`
  )
  .then(addLocaleData)
  .catch(err => {
    console.log(`Failed to load ${possibleLocale}, fallback on en`);
    import(`react-intl/locale-data/en`).then(addLocaleData);
  })
  .finally(() => {
    helpcenterApp.run();
    window.HelpcenterBundle = helpcenterApp;
  });
