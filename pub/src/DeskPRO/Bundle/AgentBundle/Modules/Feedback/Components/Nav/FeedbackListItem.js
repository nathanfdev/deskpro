import React, { Component, PropTypes } from 'react';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class FeedbackListItem extends Component {
  static propTypes = {

    // Label is also used as "itemId" of underlying ListItemStatefulContainer, so labels must be unique
    label: PropTypes.string.isRequired,
    children: PropTypes.node
  };

  render() {
    // @todo common urlSanitize() helper replacing spaces and reserved characters
    const itemId = this.props.label.replace(/\s/g, '_');

    return (
      <ListItemStatefulContainer groupId="nav" itemId={itemId} {...this.props}>
        {this.props.children}
      </ListItemStatefulContainer>
    );
  }
}
