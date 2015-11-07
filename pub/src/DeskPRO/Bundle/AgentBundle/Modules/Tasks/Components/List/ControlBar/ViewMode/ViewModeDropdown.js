import React, { PropTypes } from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import { changeView } from '../../../../Actions/tasksActions';
import jQuery from 'jquery';

export class ViewModeDropdown extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentView: PropTypes.string.isRequired,
    viewModeOptions: PropTypes.array.isRequired
  };

  onChangeView = type => {
    this.props.dispatch(changeView(type));
  };

  render() {
    const { viewModeOptions, currentView } = this.props;

    return (
      <Menu>
        {jQuery.map(viewModeOptions, (option, type) =>
          <Item key={type}
                label={option.label}
                isActive={currentView === type}
                onClick={this.onChangeView.bind(this, type)}
                icon={option.icon} />
        )}
      </Menu>
    );
  }
}
