import React, { PropTypes } from 'react';
import ReactTooltip from 'react-tooltip';

class User extends React.Component {
  static propTypes = {
    src: PropTypes.string
  };

  render() {
    const {src} = this.props;
    return <div className="user">
      <div data-tip data-for="user-menu" data-place="bottom" data-delay-hide={1000} data-event="click">
        <img className="ui circular image" src={src} />
        <i className="dropdown icon"/>
      </div>
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
export default User;