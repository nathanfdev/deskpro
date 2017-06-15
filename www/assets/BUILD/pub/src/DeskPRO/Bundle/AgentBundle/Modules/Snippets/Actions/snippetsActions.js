import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { addToCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const getSnippet = createAction(
  'SNIPPETS_GET_SNIPPET',
  id => dispatch => new Promise(
    (resolve, reject) => {
      repository('Snippets').getSnippet(id)
        .then((promise) => {
          dispatch(addToCollection('Snippets', 'all', [promise.data.data]));
          resolve(promise.data.data);
        },
          response => reject(response)
        );
    }
  )
);
export const saveSnippet = createAction(
  'SNIPPETS_SAVE_SNIPPET',
  data => dispatch => new Promise(
      (resolve, reject) => {
        repository('Snippets').saveSnippet(data)
          .then((promise) => {
            if (data.id) {
              dispatch(getSnippet(data.id)).then((snippet) => {
                resolve(snippet);
              });
            } else {
              dispatch(addToCollection('Snippets', 'all', [promise.data.data]));
              resolve(promise.data.data);
            }
          },
          response => reject(response)
        );
      }
    )
);
