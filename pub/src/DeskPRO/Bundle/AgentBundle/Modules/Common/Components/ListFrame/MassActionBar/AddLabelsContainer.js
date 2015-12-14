import React, { Component, PropTypes } from 'react';
import { Item } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';

export class AddLabelsContainer extends Component {
  static propTypes = {
    option: PropTypes.object.isRequired
  };

  render() {
    const {option} = this.props;
    return (
      <Item label={option.label}
        // isActive={viewMode === type}
        // checked={viewMode === type}
        // onClick={() => dispatch(viewModeAction(type))}
            icon={option.icon}
        />
    );
  }
}