import React, {Component, PropTypes} from 'react';
import { CheckboxContainer } from './MassAction/CheckboxContainer';

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
            <CheckboxContainer {...checkbox}/>
            <ul className="dpwd-navigation-dropdown-top-row-main-list">
              {this.props.children}
            </ul>
          </div>
        </div>
      </div>
    );
  }
}
