import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadTree = createAction(
  'PUBLISH_GUIDE_LOAD_TREE',
  guideId => new Promise((resolve) => {
    repository('Guide').loadTree(guideId).then((promise) => {
      resolve({ id: guideId, tree: promise.getData() });
    });
  })
);

export const saveTree = createAction(
  'PUBLISH_GUIDE_SAVE_TREE',
  (guideId, treeData) => new Promise(
    (resolve, reject) => repository('Guide').saveTree(guideId, treeData)
        .success(() => resolve())
        .error(response => reject(response))
  )
);
