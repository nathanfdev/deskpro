import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadFiles = createAction(
  'TICKET_ARCHIVE_LOAD_FILES',
  authId => new Promise((resolve) => {
    repository('Blob').loadFiles(authId).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);
