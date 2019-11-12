import './publicPath';
import 'react-hot-loader/patch';
import { portalApp } from './PortalApp';
import $ from 'jquery';

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

portalApp.run();
window.PortalBundle = portalApp;
