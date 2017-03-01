import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadTree = createAction(
  'PUBLISH_MANUAL_LOAD_TREE',
  manualId => new Promise((resolve) => {
    repository('Manual').loadTree(manualId).then((promise) => {
      resolve({ id: manualId, tree: promise.getData() });
    });
  })
);

export const saveTree = createAction(
  'PUBLISH_MANUAL_SAVE_TREE',
  (manualId, treeData) => new Promise(
    (resolve, reject) => repository('Manual').saveTree(manualId, treeData)
        .success(() => resolve())
        .error(response => reject(response))
  )
);
