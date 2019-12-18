import './publicPath';
import 'react-hot-loader/patch';
import { portalApp } from './PortalApp';
import { addLocaleData } from 'react-intl';

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
