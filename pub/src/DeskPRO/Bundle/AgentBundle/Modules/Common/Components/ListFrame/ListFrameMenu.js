import React, {Component, PropTypes} from 'react';
import ReactCSSTransitionGroup from 'react-addons-css-transition-group';
import { MassActionsCheckboxContainer } from './MassActionsCheckboxContainer';

export class ListFrameMenu extends Component {

  static propTypes = {
    children: PropTypes.any.isRequired,
    checkbox: PropTypes.shape({
      count: PropTypes.number.isRequired,
      action: PropTypes.func.isRequired
    })
  };

  render() {
    const { checkbox } = this.props;

    return (
      <div className="control-bar">
        <div className="ticket-controls-bulk-editing">
          <div className="dpwd-navigation-dropdown-top-row">
            <MassActionsCheckboxContainer {...checkbox}/>
            <ReactCSSTransitionGroup transitionName="example"
                                     transitionEnterTimeout={500}
                                     transitionLeaveTimeout={100}>
              {this.props.children}
            </ReactCSSTransitionGroup>
          </div>
        </div>
      </div>
    );
  }
}
