const path           = require('path');
const reducerRefresh = require('./loader').refreshBundle;

reducerRefresh('App', path.join(__dirname, '../../src/DeskPRO/Bundle/AppBundle'));
reducerRefresh('Admin', path.join(__dirname, '../../src/DeskPRO/Bundle/AdminBundle'));
reducerRefresh('Report', path.join(__dirname, '../../src/DeskPRO/Bundle/ReportBundle'));
reducerRefresh('Agent', path.join(__dirname, '../../src/DeskPRO/Bundle/AgentBundle'));
reducerRefresh('Demo', path.join(__dirname, '../../src/DeskPRO/Bundle/DemoBundle'));
reducerRefresh('Widget', path.join(__dirname, '../../src/DeskPRO/Bundle/WidgetBundle'));