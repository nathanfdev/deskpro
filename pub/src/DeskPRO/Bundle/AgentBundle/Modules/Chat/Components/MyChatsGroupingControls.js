import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../Actions/chatConversationsNavFrameActions';

@connect(state => ({
  isVisible: state.ChatConversationsNavFrame.isMyChatsGroupingControlVisible,
}))
export class MyChatsGroupingControls extends React.Component {

  render() {
    const className = this.props.isVisible ? 'sidebar-hover show' : 'sidebar-hover hide';

    const changeMyChatsGrouping = (e) => { this.changeMyChatsGrouping(e) };

    return (
      <section className={className}>
        <div className="sidebar-hover-content">
          <div className="sidebar-hover-header">
            <i className="fa fa-tag"></i>
            <span>&nbsp;</span>
            <span>My Chats</span>
          </div>
          <form>
            <p>
              <label>Grouping Options:</label>
              <select onChange={changeMyChatsGrouping}>
                <option value="date_period">Date Created</option>
                <option value="department">Department</option>
              </select>
            </p>
          </form>
        </div>
      </section>
    );
  }

  changeMyChatsGrouping(e) {
    const options = e.target.options;
    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        this.props.dispatch(actions.changeMyChatsGrouping(options[i].value));
      }
    }
  }
}
