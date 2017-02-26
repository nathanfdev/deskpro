import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const uploadFile = createAction(
  'PUBLISH_EDITOR_UPLOAD_FILE',
  (data, callback) => new Promise(
    (resolve, reject) => repository('Blob').uploadFile(data)
        .success((result) => {
          callback(result.data);
          resolve();
        })
        .error(response => reject(response))
  )
);
