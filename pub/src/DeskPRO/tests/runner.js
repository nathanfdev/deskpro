// import { Reducer } from "Ampliflux/reducer";
// import {createAction} from "Ampliflux/actions";

console.log("Running poor man's tests...\n");

const myclosure = (foo, bar) => {
  console.log([foo, bar]);
};

const args = [1, 2];
myclosure.apply(this, args);

// const loadFilterSets = {
//   actionType: "TOTO",
// };
// const unLoadFilterSets = {
//   actionType: "TATA",
// };
//
// class AgentTeams extends Reducer {
//   getInitialState() {
//     return {};
//   }
//
//   registerHandlers() {this
//     .r(loadFilterSets, this.filterSetsLoaded)
//     .r(unLoadFilterSets, this.setPayload('bob'));
//   }
//
//   filterSetsLoaded(state, action) {
//     let stuff = action.payload;
//     // Something really complex going on.
//     return {
//       ...state,
//       stuff
//     };
//   }
// }
//
// let teams = new AgentTeams();
// console.log(teams);
// console.log(teams.compile());
