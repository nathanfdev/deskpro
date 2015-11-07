import React, { PropTypes } from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import { changeView } from '../../../../Actions/tasksActions';

export class ViewModeDropdown extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentView: PropTypes.string.isRequired,
    viewModeOptions: PropTypes.array.isRequired
  };

  onChangeView = option => {
    this.props.dispatch(changeView(option.type));
  };

  renderOption(option, index) {
    return (
      <Item key={index}
            label={option.label}
            isActive={this.props.currentView === option.type}
            onClick={this.onChangeView.bind(this, option)}
            icon={option.icon} />
    );
  }

  render() {
    const { viewModeOptions } = this.props;

    return (
      <Menu>
        {viewModeOptions.map((option, index) => this.renderOption(option, index))}
      </Menu>
    );
  }
}
