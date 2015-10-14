import React, { Component, PropTypes } from 'react';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class FeedbackListItem extends Component {
  static propTypes = {
    itemId: PropTypes.string.isRequired,
    children: PropTypes.node
  };

  render() {
    return (
      <ListItemStatefulContainer groupId="nav" itemId={this.props.itemId} {...this.props}>
        {this.props.children}
      </ListItemStatefulContainer>
    );
  }
}
