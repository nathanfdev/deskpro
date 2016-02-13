import * as People from './People';
import _ from 'lodash';

export function loadAgents(options = {}) {
  return People.loadPeople(_.merge(options, {is_agent: true}));
}

export function loadAgent(id) {
  return People.loadPerson(id);
}
