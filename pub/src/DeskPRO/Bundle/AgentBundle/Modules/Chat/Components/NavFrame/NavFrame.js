import React from 'react';
import { NavFrame as BaseNavFrame } from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/NavFrame';
import { NavFrameHeader } from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/NavFrameHeader';
import { ChatsList } from './ChatsList/ChatsList';
import { ChatsListGroupingControl } from './ChatsListGroupingControl';

export class NavFrame extends React.Component {
  render() {
    const {lists, grouping, changeGrouping, toggleGroupingVisibility} = this.props;

    return (
      <BaseNavFrame>

        <div part="outer">
          <ChatsListGroupingControl title="My Chats"
                                    options={grouping.my.options}
                                    visible={grouping.my.visible}
                                    onChange={changeGrouping('my')} />
          <ChatsListGroupingControl title="All Chats"
                                    options={grouping.all.options}
                                    visible={grouping.all.visible}
                                    onChange={changeGrouping('all')} />
        </div>

        <div part="inner">
          <NavFrameHeader icon="fa-comments-o" title="Chat" />

          <div className="sidebar-list sidebar-list-filters">
            <ChatsList data={lists.my} toggleGroupingVisibility={toggleGroupingVisibility('my')} />
            <ChatsList data={lists.all} toggleGroupingVisibility={toggleGroupingVisibility('all')} />
          </div>
        </div>

      </BaseNavFrame>
    );
  }
}
