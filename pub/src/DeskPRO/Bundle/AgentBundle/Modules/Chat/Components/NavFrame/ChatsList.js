import React from 'react';
import { ChatsListItemContainer } from './ChatsListItemContainer';

export class ChatsList extends React.Component {

  render() {
    const {data, toggleGroupingVisibility} = this.props;

    return (
      <section className="sidebar-list tasks-nav-groups">
        <div className="list-sidebar-title">
          My Chats
          <div className="list-counter-bucket">
            <a className="list-counter-dropdown active" href="#" onClick={toggleGroupingVisibility}>
              <span>&nbsp;</span>
              <i className="fa fa-angle-down"></i>
            </a>
            <a className="list-counter active" href="#">{data.total}</a>
          </div>
        </div>

        <ul>
          {data.items.map(item =>
              <ChatsListItemContainer groupBy={data.groupBy} group={item.group} count={item.count} key={item.group} />)}
        </ul>
      </section>
    );
  }
}
