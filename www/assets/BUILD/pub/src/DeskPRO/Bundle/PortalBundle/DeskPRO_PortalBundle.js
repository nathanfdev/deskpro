import './publicPath';
import 'react-hot-loader/patch';
import { portalApp } from './PortalApp';
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

const possibleLocale = (window.DESKPRO_LOCALE || 'en').replace(/-/, '_').split(/_/)[0] || 'en';

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
    portalApp.run();
    window.PortalBundle = portalApp;
  });
