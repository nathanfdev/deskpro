import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import Immutable from 'immutable';
import store from '../../../Services/store';

export const getDepartmentAgents = (department) => {
  let id;
  if (department instanceof Immutable.Map) {
    id = department.get('id');
  } else {
    id = department;
  }

  return collectionSelectorFactory('Person', `department_${id}`)(store.getState());
};
