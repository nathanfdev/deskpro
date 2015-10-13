import React, {PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import { toggleViewMode } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';

const ViewSwitcherDropdown = React.createClass({

  propTypes: {
    viewModeOptions: PropTypes.object.isRequired,
    currentViewMode: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  },

  mixins: [require('react-onclickoutside')],

  toggleView: function toggleView(newView) {
    const {dispatch} = this.props;
    dispatch(toggleViewMode(newView));
  },

  handleClickOutside: function handleClickOutside() {
    this.props.toggleDropdown();
  },

  renderOptions: function renderOptions() {
    const {viewModeOptions, currentViewMode} = this.props;
    return (
      viewModeOptions.map((option, index)=>
          <Item
            key={index}
            isActive={currentViewMode.field === option.get('field')}
            checked={currentViewMode.field === option.get('field')}
            onClick={this.toggleView.bind(this, option.get('field'))}
            icon={option.get('icon')}
            >
            {option.get('label')}
          </Item>
      )
    );
  },

  render: function render() {
    return (
      <Menu>
        {this.renderOptions()}
        <MenuFooter>
          <div className="dpw-navigation-dropdown-options-link">
            <a href="#">View Options <i className="fa fa-cog"></i></a>
          </div>
        </MenuFooter>
      </Menu>
    );
  }

});

module.exports = ViewSwitcherDropdown;
