import PropTypes from 'prop-types';
import React, { Component } from 'react';
import ReactCSSTransitionGroup from 'react-addons-css-transition-group';
import { MassActionsCheckboxContainer } from './MassActionsCheckboxContainer';

export class ListFrameMenu extends Component {

  static propTypes = {
    children: PropTypes.any.isRequired
  };

  render() {
    return (
      <div className="control-bar">
        <div className="ticket-controls-bulk-editing">
          <div className="dpwd-navigation-dropdown-top-row">
            <MassActionsCheckboxContainer />
            <ReactCSSTransitionGroup transitionName="example" transitionEnterTimeout={500} transitionLeaveTimeout={100}>
              {this.props.children}
            </ReactCSSTransitionGroup>
          </div>
        </div>
      </div>
    );
  }
}
