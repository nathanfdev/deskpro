import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll, addToCollection, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';

export const loadQueues = createAction(
  'TWILIO_LOAD_QUEUES',
  () => dispatch => dispatch(loadAll('TwilioQueue'))
);

export const createQueue = createAction(
  'TWILIO_CREATE_QUEUE',
  data => dispatch => repository('TwilioQueue').create(data).success((response) => {
    dispatch(addToCollection('TwilioQueue', 'all', Immutable.List([Immutable.fromJS(response.data)])));
  })
);

export const updateQueue = createAction(
  'TWILIO_UPDATE_QUEUE',
  (id, data) => dispatch => repository('TwilioQueue').update(data, id).success(() => {
    dispatch(updateCollection('TwilioQueue', Immutable.List([Immutable.fromJS({ ...data, id })]), 'merge'));
  })
);
