import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { updateRoutingState } from '../../../../Application/Actions/routingActions';

@connect(state => ({
  state: state.Application.routing.get('hash')
}))
export class ListItemStatefulContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    state: PropTypes.object.isRequired,
    groupId: PropTypes.string.isRequired,
    itemId: PropTypes.string.isRequired
  };

  render() {
    const props = this.props;
    const { groupId, itemId, dispatch, onClick } = props;
    const child = this.props.children;
    const childProps = child.props;

    return React.cloneElement(child, {
      ...childProps,

      // declaring "active" property accordingly to the URL state
      active: props.state.getIn([groupId, 'active']) === itemId,

      // decorating original "onClick" with additional URL state saving functionality
      onClick: event => {
        event.preventDefault();
        event.stopPropagation();

        onClick(event);
        dispatch(updateRoutingState(groupId, 'active', itemId));
      }
    });
  }
}
