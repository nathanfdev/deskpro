import * as LabelsListActions from "../Actions/LabelsListActions";
import { Reducer } from "Ampliflux/reducers";

export default class FilterSetslist extends Reducer {
  getInitialState() {
    return {
    	LabelsList: [],
    };
  }

  registerHandlers() {this
    .r(LabelsListActions.loadLabels, this.labelsLoaded)
  }
  
  labelsLoaded(state, action) {
    const flat_data = action.payload.data;
    let grouped_data = {};
    
    for(let k in flat_data) {
      const label = flat_data[k];
      let letter = label[0];
      
      if(typeof grouped_data[letter] === 'undefined') {
        grouped_data[letter] = {
          letter: letter,
          labels: [label]
        };
      } else {
        grouped_data[letter].labels.push(label);
      }
    }
    
    const final_data = Object.keys(grouped_data).map(key => (grouped_data[key]));
    
    return {
      ...state,
      LabelsList: final_data
    };
  }
}
