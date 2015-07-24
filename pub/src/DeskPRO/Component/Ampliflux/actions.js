export function createAction(action_type, action = null) {
  let handler = null;
  if(!action) { // Dumb action
    handler = () => {
      //console.log(action_type); // This is very handy for debugging.
      return (dispatch) => dispatch({
        type: action_type,
        payload: null
      });
    }
  } else {
    handler = (...stuff) => {
      return (dispatch) => {
        const trigger = (payload = null, type = null) => {
          if(!type) {
            type = action_type;
          }
          //console.log(type); // This is very handy for debugging.
          return dispatch({
            type: type,
            payload: payload
          });
        };
      
        const args = [trigger, ...stuff];
        return action.apply(this, args);
      };
    };
  }
  
  handler.actionType = action_type;  
  return handler;
}
