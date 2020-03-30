import './publicPath';
import 'react-hot-loader/patch';
import { helpcenterApp } from './HelpcenterApp';
import $ from 'jquery';
import { addLocaleData } from 'react-intl';

(function () {
  // based on https://github.com/matthew-andrews/Promise.prototype.finally

  // Get a handle on the global object
  let globalObject;
  if (typeof global !== 'undefined') {
    globalObject = global;
  } else if (typeof window !== 'undefined' && window.document) {
    globalObject = window;
  }

  // check if the implementation is available
  if (typeof Promise.prototype['finally'] === 'function') {
    return;
  }

  // implementation
  globalObject.Promise.prototype['finally'] = function (callback) {
    const constructor = this.constructor;

    return this.then(function (value) {
      return constructor.resolve(callback()).then(function () {
        return value;
      });
    }, function (reason) {
      return constructor.resolve(callback()).then(function () {
        throw reason;
      });
    });
  };
}());

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

let possibleLocale = 'en';
if (window.DESKPRO_LOCALE) {
  possibleLocale = window.DESKPRO_LOCALE.replace(/-/, '_').split(/_/)[0] || 'en';
}

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
