import { url } from '../helpers';
import { commands, elements } from './agentLogin';

module.exports = {
  url: url('/agent/login?return=/admin/admin-interface'),
  commands,
  elements
};
