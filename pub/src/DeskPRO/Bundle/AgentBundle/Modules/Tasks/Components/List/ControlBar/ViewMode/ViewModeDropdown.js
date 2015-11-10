import React, { PropTypes } from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import jQuery from 'jquery';

export class ViewModeDropdown extends React.Component {

  static propTypes = {
    onChangeView: PropTypes.func.isRequired,
    currentView: PropTypes.string.isRequired,
    viewModeOptions: PropTypes.array.isRequired
  };

  render() {
    const { viewModeOptions, currentView, onChangeView } = this.props;

    return (
      <Menu>
        {jQuery.map(viewModeOptions, (option, type) =>
          <Item key={type}
                label={option.label}
                isActive={currentView === type}
                checked={currentView === type}
                onClick={onChangeView.bind(this, type)}
                icon={option.icon} />
        )}
      </Menu>
    );
  }
}
