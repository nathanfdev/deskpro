import { createSelector } from 'reselect';
import Immutable from 'immutable';

export const filterAppConfig =  ({DeskproAppStore: {Main:state}}) => state.get('apps').toJS();

const filterContexts =  ({DeskproAppStore: {Main:state}}) => state.get('contexts');
export const contextsStateSelector = createSelector([ filterContexts ], contexts => contexts.toJS() );

