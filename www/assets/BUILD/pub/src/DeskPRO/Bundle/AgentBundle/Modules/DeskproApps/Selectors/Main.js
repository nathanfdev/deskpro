import { AppsConfig } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';

export const filterAppConfig =  ({ DeskproApps: { Main:state } }) => state.get('apps').toJS();

export const filterApiToken =  ({ DeskproApps: { Main:state } }) => state.get('apiToken');

/**
 * @param state
 * @return {AppsConfig}
 */
export const filterAppstoreConfig =  ({ DeskproApps: { Main:state } }) => {
  const configJS = state.get('config').toJS();
  return new AppsConfig(configJS);
};

export const filterAppManifestsConfig = ({ DeskproApps: { Main:state } }) => {
  const manifests = state.get('apps').get('apps');
  return manifests.toJS();
};

const filterContexts =  ({ DeskproApps: { Main:state } }) => state.get('contexts');

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

