import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { Item } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import { MenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';

export class ViewModeMenu extends Component {

  static propTypes = {
    dispatch:       PropTypes.func.isRequired,
    expandOptions:  PropTypes.func.isRequired,
    viewModeAction: PropTypes.func.isRequired,
    viewMode:       PropTypes.string.isRequired,
    options:        PropTypes.object.isRequired
  };

  onClick = event => {
    event.preventDefault();
    this.props.expandOptions();
  };

  render() {
    const { viewMode, options, dispatch, viewModeAction } = this.props;

    return (
      <Menu>
        {Object.entries(options).map(([type, option]) =>
          <Item
            key={type}
            label={option.label}
            isActive={viewMode === type}
            checked={viewMode === type}
            onClick={() => dispatch(viewModeAction(type))}
            icon={option.icon}
          />
        )}
        <MenuFooter>
          <div className="dpw-navigation-dropdown-options-link">
            <a href="#" ref="optionsButton" onClick={this.onClick}>
              View Options <i className="fa fa-cog" />
            </a>
          </div>
        </MenuFooter>
      </Menu>
    );
  }
}
