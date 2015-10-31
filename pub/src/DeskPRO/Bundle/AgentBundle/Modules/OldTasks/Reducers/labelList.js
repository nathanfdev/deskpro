import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  labelCharacters: [],
  labelList: []
};

export default createReducer(initialState, {
  [TaskListActions.loadLabels]: (state, payload) => {
    const sortedLabels = {};
    const sortedCharacters = [];

    if (typeof payload.data !== 'undefined' && typeof payload.data.forEach === 'function') {
      payload.data.forEach((key) => {
        const label = key.label.toLowerCase();
        const currentCharacter = label.substr(0, 1).toUpperCase();

        // .keys() not available, so we push it ourselves
        if (sortedCharacters.indexOf(currentCharacter) === -1) {
          sortedCharacters.push(currentCharacter);
        }

        if (typeof sortedLabels[currentCharacter] === 'undefined') {
          sortedLabels[currentCharacter] = [];
        }

        sortedLabels[currentCharacter].push(key);
      });
    }

    const withCharacters = state.set('labelCharacters', sortedCharacters);
    return withCharacters.set('labelList', sortedLabels);
  }
});
