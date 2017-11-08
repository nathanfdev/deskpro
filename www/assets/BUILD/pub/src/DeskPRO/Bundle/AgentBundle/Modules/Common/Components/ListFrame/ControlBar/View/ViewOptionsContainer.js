import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { Item } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import { ViewOptionsList } from './ViewOptionsList';

@connect()
export class ViewOptionsContainer extends Component {

  static propTypes = {
    dispatch:                PropTypes.func.isRequired,
    onViewFieldsMenuUnmount: PropTypes.func,
    toggleFieldVisibility:   PropTypes.func,
    changeFieldOrder:        PropTypes.func,
    viewMode:                PropTypes.string.isRequired,
    options:                 PropTypes.object.isRequired
  };

  componentWillUnmount() {
    const { dispatch, onViewFieldsMenuUnmount } = this.props;

    if (onViewFieldsMenuUnmount) {
      dispatch(onViewFieldsMenuUnmount());
    }
  }

  renderItem = (type, option) => {
    const { viewMode, toggleFieldVisibility, changeFieldOrder } = this.props;
    return (
      <div key={type}>
        <Item
          discMarked
          label={option.label}
          widgetClass="dpw-navigation-dropdown-column-list-item"
          isActive={viewMode === type}
        />
        <ViewOptionsList
          {...option}
          type={type}
          toggleFieldVisibility={toggleFieldVisibility}
          changeFieldOrder={changeFieldOrder}
        />
      </div>
    );
  };

  render() {
    const { options } = this.props;

    return (
      <Menu widgetClass="dpw-navigation-dropdown-secondary">
        {Object.entries(options).map(([type, option]) => this.renderItem(type, option))}
      </Menu>
    );
  }
}

