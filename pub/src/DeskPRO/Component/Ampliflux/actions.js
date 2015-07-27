export function createAction(action_type, action = null) {
  if(typeof(action_type) == 'function' && !action) { // The action_type was omitted; we create one implicitly.
    action = action_type;
    action_type = "flux-randomaction-" + Math.floor(Math.random() * 1000000000 + 1);
  }
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
          else if(typeof type == 'function') {
            type = type.actionType;
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
