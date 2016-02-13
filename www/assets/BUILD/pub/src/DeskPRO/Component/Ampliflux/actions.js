export function createAction(action_type, action = null) {
  if(typeof(action_type) == 'function' && !action) { // The action_type was omitted; we create one implicitly.
    action = action_type;
    action_type = "flux-randomaction-" + Math.floor(Math.random() * 1000000000 + 1);
  }
  console.warn("Ampliflux/actions.js createAction() is deprecated [" + action_type + "]");
  let handler = null;
  if(!action) { // Dumb action
    handler = (payload = null) => {
      //console.log(action_type); // This is very handy for debugging.
      return (dispatch) => dispatch({
        type: action_type,
        payload: payload
      });
    }
  } else {
    handler = (...stuff) => {
      return (dispatch) => {
        const trigger = (dispatched = null, type = null) => {
          if (typeof dispatched === 'function') {
            dispatch(dispatched)
          } else {
            let to_dispatch = { type: type, payload: dispatched };

            if(!type) {
              to_dispatch.type = action_type;
            } else if(typeof type == 'function' && type.actionType !== undefined) {
              to_dispatch.type = type.actionType;
            } else {
              to_dispatch = type;
            }

            //console.log(type); // This is very handy for debugging.
            return dispatch(to_dispatch);
          }
        };
      
        const args = [trigger, ...stuff];
        return action.apply(this, args);
      };
    };
  }
  
  handler.actionType = action_type;  
  return handler;
}
