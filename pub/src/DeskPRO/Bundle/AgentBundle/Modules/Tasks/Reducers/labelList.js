import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	labelList: null,
    labelCharacters: []
};

const r = handleActions({
	[ActionTypes.TASKS_LOAD_LABELS]: function(state, action) {
        let payload = action.payload.data;

        let sortedLabels = {};
        let sortedCharacters = [];

        // Sort labels according to their respective first letters
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

        return {
            ...state,
            labelList: sortedLabels,
            labelCharacters: sortedCharacters
        }
    }
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
