export function createAction(action_type, action) {
  let handler = () => {
    return (dispatch) => {
      const trigger = (payload = null) => dispatch({
        type: action_type,
        payload: payload
      });
      return action(trigger, dispatch);
    }
  };
  handler.actionType = action_type;
  
  return handler;
}
