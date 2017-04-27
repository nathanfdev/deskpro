import { createSelector } from 'reselect';

export const filterAppConfig =  ({DeskproAppStore: {Main:state}}) => state.get('apps').toJS();
export const filterAppManifestsConfig =  ({DeskproAppStore: {Main:state}}) => state.get('apps').get('manifests').toJS();

const filterContexts =  ({DeskproAppStore: {Main:state}}) => state.get('contexts');
export const contextsStateSelector = createSelector([ filterContexts ], contexts => contexts.toJS() );

