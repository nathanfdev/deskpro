import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { updateRoutingState } from '../../../../Application/Actions/routingActions';
import { ListItem } from './ListItem';

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
    const newProps = {
      ...props,

      // declaring "active" property accordingly to the URL state
      active: props.state.getIn([props.groupId, 'active']) === props.itemId,

      // decorating original "onClick" with additional URL state saving functionality
      onClick(event) {
        event.preventDefault();
        event.stopPropagation();

        props.onClick(event);
        props.dispatch(updateRoutingState(props.groupId, 'active', props.itemId));
      }
    };

    return (
      <ListItem {...newProps} />
    );
  }
}
