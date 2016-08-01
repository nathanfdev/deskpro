import React, { PropTypes } from 'react';
import ReactTooltip from 'react-tooltip';

export class Hello extends React.Component {
  static propTypes = {
    person: PropTypes.object.isRequired
  };

  render() {
    return <div>
      <span data-tip data-for="user-menu" data-place="bottom" data-delay-hide={1000} data-event="click">Hello {this.props.person.fname}  {this.props.person.lname}</span>
      <ReactTooltip id="user-menu">
        <ul>
          <li><i className="icon setting"/> Preferences</li>
          <li><i className="icon help circle"/> Help</li>
          <li><i className="icon reply"/> Log out</li>
        </ul>
      </ReactTooltip>
      </div>
  }
}