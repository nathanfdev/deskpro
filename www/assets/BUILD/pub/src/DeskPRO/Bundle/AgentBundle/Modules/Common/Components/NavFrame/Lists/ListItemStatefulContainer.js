import PropTypes from 'prop-types';
import React from 'react';
import { pureRender } from 'Ampliflux';
import { connect } from 'react-redux';
import { updateRoutingState } from '../../../../Application/Actions/routingActions';

@connect(state => ({
  state: state.Application.routing.get('hash')
}))

@pureRender

export class ListItemStatefulContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    state:    PropTypes.object.isRequired,
    active:   PropTypes.string,
    groupId:  PropTypes.string.isRequired,
    itemId:   PropTypes.string.isRequired,
    onClick:  PropTypes.func.isRequired,
    children: PropTypes.node.isRequired
  };

  render = () => {
    const { state, groupId, itemId, dispatch, onClick, active = 'active' } = this.props;
    const child      = this.props.children;
    const childProps = child.props;

    return React.cloneElement(child, {
      ...childProps,

      // declaring "active" property accordingly to the URL state
      active: state.getIn([groupId, active]) === itemId,

      // decorating original "onClick" with additional URL state saving functionality
      onClick: event => {
        event.preventDefault();
        event.stopPropagation();

        onClick(event);
        dispatch(updateRoutingState(groupId, active, itemId));
      }
    });
  }
}
