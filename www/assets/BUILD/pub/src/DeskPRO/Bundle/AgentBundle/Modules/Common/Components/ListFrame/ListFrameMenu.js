import PropTypes from 'prop-types';
import React, { Component } from 'react';
import CSSTransitionGroup from 'react-transition-group';
import { MassActionsCheckboxContainer } from './MassActionsCheckboxContainer';

export class ListFrameMenu extends Component {

  static propTypes = {
    children: PropTypes.node.isRequired
  };

  render() {
    return (
      <div className="control-bar">
        <div className="ticket-controls-bulk-editing">
          <div className="dpwd-navigation-dropdown-top-row">
            <MassActionsCheckboxContainer />
            <CSSTransitionGroup transitionName="example" transitionEnterTimeout={500} transitionLeaveTimeout={100}>
              {this.props.children}
            </CSSTransitionGroup>
          </div>
        </div>
      </div>
    );
  }
}
