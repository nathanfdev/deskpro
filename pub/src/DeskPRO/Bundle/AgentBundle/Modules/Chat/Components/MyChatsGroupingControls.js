import React from 'react';
import { connect } from 'redux/react';

@connect(state => ({
  isVisible: state.ChatConversationsNavFrame.isMyChatsGroupingControlVisible,
}))
export class MyChatsGroupingControls extends React.Component {

  render() {
    const className = this.props.isVisible ? 'sidebar-hover show' : 'sidebar-hover hide';

    return (
      <section className={className}>
        <div className="sidebar-hover-content">
          <div className="sidebar-hover-header">
            <i className="fa fa-tag"></i>
            <span>&nbsp;</span>
            <span>My Tickets</span>
          </div>
          <form>
            <p>
              <label>Grouping Options:</label>
              <select>
                <option value="date_period">Date Created</option>
                <option value="department">Department</option>
              </select>
            </p>
          </form>
        </div>
      </section>
    );
  }
}
