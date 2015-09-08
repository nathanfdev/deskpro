import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class LabelList extends Reducer {
  getInitialState() {
    return {
    	labelList: null,
      labelCharacters: []
    };
  }
  
  labelsLoaded(state, action) {
    let payload = action.payload.data;

    let sortedLabels = {};
    let sortedCharacters = [];

    // Sort labels according to their respective first letters
    if (typeof payload.forEach === 'function') {
      payload.forEach(function(key){
        let label = key.label.toLowerCase();
        let currentCharacter = label.substr(0, 1).toUpperCase();

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

    return {
        ...state,
        labelList: sortedLabels,
        labelCharacters: sortedCharacters
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadLabels, this.labelsLoaded)
  }
}
