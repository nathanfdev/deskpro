const { join }       = require('path');
const reducerRefresh = require('./loader').refreshBundle;

reducerRefresh('App', join(__dirname, '../../src/DeskPRO/Bundle/AppBundle'));
reducerRefresh('Admin', join(__dirname, '../../src/DeskPRO/Bundle/AdminBundle'));
reducerRefresh('Report', join(__dirname, '../../src/DeskPRO/Bundle/ReportBundle'));
reducerRefresh('Agent', join(__dirname, '../../src/DeskPRO/Bundle/AgentBundle'));
reducerRefresh('Demo', join(__dirname, '../../src/DeskPRO/Bundle/DemoBundle'));
reducerRefresh('Widget', join(__dirname, '../../src/DeskPRO/Bundle/WidgetBundle'));