import { url } from '../helpers';
import { commands, elements } from './agentLogin';

module.exports = {
  url: url('/agent/login?return=/admin/start'),
  commands,
  elements
};
