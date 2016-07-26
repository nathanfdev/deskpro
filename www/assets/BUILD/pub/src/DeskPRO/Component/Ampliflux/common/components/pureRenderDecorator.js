import shallowCompare from 'react-addons-shallow-compare';

/**
 * Tells if a component should update given it's next props and state.
 *
 * @param {object} nextProps Next props
 * @param {object} nextState Next state
 * @return {bool} shallowCompare()
 */
function shouldComponentUpdate(nextProps, nextState) {
  return shallowCompare(this, nextProps, nextState);
}

/**
 * Adds default shouldComponentUpdate() implementation to a given component
 *
 * @param {object} component React Component
 * @return {bool} If shouldComponentUpdate
 */
export function pureRender(component) {
  component.prototype.shouldComponentUpdate = shouldComponentUpdate;
}
