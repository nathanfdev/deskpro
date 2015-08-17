import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../Actions/chatConversationsNavFrameActions';

@connect(state => ({
  isVisible: state.ChatConversationsNavFrame.isAllChatsGroupingControlVisible,
}))
export class AllChatsGroupingControls extends React.Component {

  render() {
    const className = this.props.isVisible ? 'sidebar-hover show' : 'sidebar-hover hide';

    const changeAllChatsGrouping = (e) => { this.changeAllChatsGrouping(e) };

    return (
      <section className={className}>
        <div className="sidebar-hover-content">
          <div className="sidebar-hover-header">
            <i className="fa fa-tag"></i>
            <span>&nbsp;</span>
            <span>All Chats</span>
          </div>
          <form>
            <p>
              <label>Grouping Options:</label>
              <select onChange={changeAllChatsGrouping}>
                <option value="agent">Agent</option>
                <option value="department">Department</option>
                <option value="date_period">Date Created</option>
              </select>
            </p>
          </form>
        </div>
      </section>
    );
  }

  changeAllChatsGrouping(e) {
    const options = e.target.options;
    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        this.props.dispatch(actions.changeAllChatsGrouping(options[i].value));
      }
    }
  }
}
