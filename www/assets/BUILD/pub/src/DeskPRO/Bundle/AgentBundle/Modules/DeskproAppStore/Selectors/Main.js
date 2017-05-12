export const filterAppConfig =  ({DeskproAppStore: {Main:state}}) => state.get('apps').toJS();
export const filterAppManifestsConfig =  ({DeskproAppStore: {Main:state}}) => state.get('apps').get('manifests').toJS();
const filterContexts =  ({DeskproAppStore: {Main:state}}) => state.get('contexts');

const createContextsStateSelector = function (oldContexts) {
  return (state) => {
    const contexts = filterContexts(state);
    // no change detected
    if (oldContexts === contexts) { return null; }

    const oldKeys = oldContexts ? oldContexts.keySeq().toArray() : [];
    const addedKeys =  contexts.keySeq().toArray().filter(key => oldKeys.indexOf(key) < 0 );
    const addedContexts = addedKeys.length ? contexts.filter((v, k) => addedKeys.indexOf(k) !== -1) : null;

    oldContexts = contexts;
    return addedContexts && addedContexts.size ? addedContexts : null;
  };
};
export const newContextsStateSelector = createContextsStateSelector(null);

