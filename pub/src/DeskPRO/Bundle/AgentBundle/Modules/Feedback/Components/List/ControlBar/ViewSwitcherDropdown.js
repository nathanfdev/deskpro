import React, {PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const ViewSwitcherDropdown = React.createClass({

  propTypes: {
    currentViewMode: PropTypes.string.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    toggleOptionsMenu: PropTypes.func.isRequired,
    toggleView: PropTypes.func.isRequired
  },

  mixins: [require('react-onclickoutside')],

  handleClickOutside: function handleClickOutside() {
    this.props.toggleDropdown();
  },

  render: function render() {
    const { currentViewMode, toggleOptionsMenu, toggleView } = this.props;

    return (
      <Menu>
        <Item
          isActive={currentViewMode === constants.VIEW_MODE_CARD}
          checked={currentViewMode === constants.VIEW_MODE_CARD}
          onClick={toggleView.bind(this, constants.VIEW_MODE_CARD)}
          icon="list"
          >
          Card view
        </Item>
        <Item
          isActive={currentViewMode === constants.VIEW_MODE_TABLE}
          checked={currentViewMode === constants.VIEW_MODE_TABLE}
          onClick={toggleView.bind(this, constants.VIEW_MODE_TABLE)}
          icon="table"
          >
          Table view
        </Item>
        <MenuFooter>
          <div className="dpw-navigation-dropdown-options-link">
            <a href="#" onClick={toggleOptionsMenu}>View Options <i className="fa fa-cog"></i></a>
          </div>
        </MenuFooter>
      </Menu>
    );
  }

});

module.exports = ViewSwitcherDropdown;
