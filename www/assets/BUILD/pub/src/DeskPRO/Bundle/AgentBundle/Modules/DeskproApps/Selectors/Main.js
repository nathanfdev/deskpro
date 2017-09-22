import { AppsConfig } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';

export const filterAppConfig =  ({ DeskproAppStore: { Main:state } }) => state.get('apps').toJS();

export const filterApiToken =  ({ DeskproAppStore: { Main:state } }) => state.get('apiToken');

export const filterAppstoreConfig =  ({ DeskproAppStore: { Main:state } }) => {
  const configJS = state.get('config').toJS();
  return new AppsConfig(configJS);
};

export const filterAppManifestsConfig = ({ DeskproAppStore: { Main:state } }) => {
  const manifests = state.get('apps').get('manifests');
  return manifests.toJS();
};

const filterContexts =  ({ DeskproAppStore: { Main:state } }) => state.get('contexts');

const createContextsStateSelector = (initialContexts) => {
  let oldContexts = initialContexts;
  return (state) => {
    const contexts = filterContexts(state);

    // no change detected
    if (oldContexts === contexts) { return { added: [], deleted: [] }; }

    const oldKeys = oldContexts ? oldContexts.keySeq().toArray() : [];
    const newKeys = contexts ? contexts.keySeq().toArray() : [];

    const addedKeys = newKeys.filter(key => oldKeys.indexOf(key) < 0);
    const deletedKeys = oldKeys.filter(key => newKeys.indexOf(key) < 0);

    const added = contexts && addedKeys.length ? contexts.filter((v, k) => addedKeys.indexOf(k) !== -1).toArray() : [];
    const deleted = oldContexts && deletedKeys.length ? oldContexts.filter((v, k) => deletedKeys.indexOf(k) !== -1).toArray() : [];

    oldContexts = contexts;
    return { added, deleted };
  };
};
export const changedContextsSelector = createContextsStateSelector(null);

