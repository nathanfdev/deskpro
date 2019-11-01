import 'react-hot-loader/patch';
import $ from 'jquery';
import 'bootstrap';
import 'popper.js';

const toolOptions = {
  template: '<div class="tooltip dp-po-tip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
};

$('[data-toggle="tooltip"]').tooltip(toolOptions);
$('[data-toggle="popover"]').popover();
$('.dropdown-toggle').dropdown();
