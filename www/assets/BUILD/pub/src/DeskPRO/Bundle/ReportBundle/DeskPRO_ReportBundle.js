import merge from 'lodash/merge';
import 'react-hot-loader/patch';
import ReportApp from './ReportApp';

// called from angular dashboard code
window.lodashMerge = merge;

// IE/Edge Hack http://stackoverflow.com/questions/1481251/what-does-document-domain-document-domain-do
document.domain = document.domain;

window.ReportBundle = ReportApp;
