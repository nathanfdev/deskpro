import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setChatsRequest = createAction('SET_CHATS_REQUEST', setRequestRecords());
