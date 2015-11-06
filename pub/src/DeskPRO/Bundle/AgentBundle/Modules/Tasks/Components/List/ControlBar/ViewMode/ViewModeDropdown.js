import React, { PropTypes } from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';

export class ViewModeDropdown extends React.Component {

  static propTypes = {
    currentViewModeOption: PropTypes.object,
    viewModeOptions: PropTypes.array
  };

  renderOption(option, index) {
    return (
      <Item key={index}
            label={option.label}
            isActive={this.props.currentViewModeOption === option}
            icon={option.icon}/>
    );
  }

  render() {
    const { viewModeOptions = [] } = this.props;

    return (
      <Menu>
        {viewModeOptions.map((option, index) => this.renderOption(option, index))}
      </Menu>
    );
  }
}
