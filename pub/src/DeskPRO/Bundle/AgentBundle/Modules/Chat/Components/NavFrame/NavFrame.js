import React from 'react';
import { NavFrameTitle } from './NavFrameTitle';
import { ChatsList } from './ChatsList/ChatsList';
import { ChatsListGroupingControl } from './ChatsListGroupingControl';

export class NavFrame extends React.Component {
  render() {
    const {lists, grouping, changeGrouping, toggleGroupingVisibility} = this.props;

    return (
      <div>
        <ChatsListGroupingControl title="My Chats"
                                  options={grouping.my.options}
                                  visible={grouping.my.visible}
                                  onChange={changeGrouping('my')} />

        <ChatsListGroupingControl title="All Chats"
                                  options={grouping.all.options}
                                  visible={grouping.all.visible}
                                  onChange={changeGrouping('all')} />

        <section className="task-nav-frame dp-nav-frame">
          <div className="sidebar-wrapper" id="sidebar-wrapper">
            <a className="collapse-button" href="#"><i className="fa fa-angle-right"></i></a>
            <span className="collapse-controls">
              <span className="disc"></span>
              <span className="disc"></span>
              <i className="fa fa-caret-right"></i>
              <span className="disc"></span>
              <span className="disc"></span>
            </span>
            <aside className="sidebar has-tabs" id="sidebar">

              <NavFrameTitle />

              <div className="sidebar-list sidebar-list-filters">
                <ChatsList data={lists.my} toggleGroupingVisibility={toggleGroupingVisibility('my')} />
                <ChatsList data={lists.all} toggleGroupingVisibility={toggleGroupingVisibility('all')} />
              </div>

            </aside>
          </div>
        </section>
      </div>
    );
  }
}
