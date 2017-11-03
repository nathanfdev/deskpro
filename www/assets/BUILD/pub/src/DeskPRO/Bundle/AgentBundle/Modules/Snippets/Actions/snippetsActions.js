import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { addToCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { replaceIds } from 'DeskPRO/Component/Util/Api';

export const getSnippet = createAction(
  'SNIPPETS_GET_SNIPPET',
  id => dispatch => new Promise(
    (resolve, reject) => {
      repository('Snippets').load(id, 'snippet_translation,blob', true)
        .then((promise) => {
          dispatch(addToCollection('Snippets', 'all', [promise.data.data]));
          resolve(promise.data.data);
        },
          response => reject(response)
        );
    }
  )
);
export const getSnippets = createAction(
  'SNIPPETS_GET_SNIPPETS',
  ids => dispatch => new Promise(
    (resolve, reject) => {
      repository('Snippets').loadBatch(ids, 'snippet_translation,blob', true)
        .then((promise) => {
          dispatch(addToCollection('Snippets', 'all', promise.data.data));
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
export const deleteSnippet = createAction(
  'SNIPPETS_SAVE_SNIPPET',
  snippetId => dispatch => new Promise(
      (resolve, reject) => {
        repository('Snippets').remove(snippetId)
          .then((promise) => {
            if (promise.status === 'success') {
              dispatch(removeFromCollection('Snippets', 'all', [snippetId]));
              resolve(promise);
            }
          },
          response => reject(response)
        );
      }
    )
);
export const addSnippetAttachment = createAction(
  'SNIPPETS_ADD_ATTACHMENT',
  blob => (dispatch) => {
    dispatch(addToCollection('SnippetBlobs', 'all', replaceIds([blob], 'blob_id')));
  }
);
export const loadSnippetLanguagePreferences = createAction(
  'SNIPPETS_LOAD_LANGUAGE_PREFERENCES',
  () => repository('PersonSetting').load('agent.ui.snippets.language_preferences').then(value => value.getData().data)
);
export const saveSnippetLanguagePreferences = createAction(
  'SNIPPETS_SAVE_LANGUAGE_PREFERENCES',
  data => repository('PersonSetting').update({ name: 'agent.ui.snippets.language_preferences', value: data })
);
export const createSnippetLanguagePreferences = createAction(
  'SNIPPETS_CREATE_LANGUAGE_PREFERENCES',
  data => repository('PersonSetting')
    .create({ name: 'agent.ui.snippets.language_preferences', value: data }).then(value => value.getData())
);
export const massActions = createAction(
  'SNIPPETS_MASS_ACTIONS',
  data => () => new Promise(
    (resolve, reject) => {
      repository('Snippets').massActions(data)
        .then((promise) => {
          resolve(promise.data.data);
        },
          response => reject(response)
        );
    }
  )
);
export const exportSnippets = createAction(
  'SNIPPETS_EXPORT',
  data => repository('Snippets').exportSnippets(data)
);
export const getUsageLog = createAction(
  'SNIPPETS_USAGE_LOG',
  snippetId => () =>
    api.sendGet(`DP_API/snippets_use?inline_sideloads=true&include=ticket_message,ticket,person&snippet_id=${snippetId}`)
);
export const getChangelog = createAction(
  'SNIPPETS_CHANGE_LOG',
  snippetId => () =>
    api.sendGet(`DP_API/snippets_change_logs?inline_sideloads=true&snippet_id=${snippetId}`)
);
