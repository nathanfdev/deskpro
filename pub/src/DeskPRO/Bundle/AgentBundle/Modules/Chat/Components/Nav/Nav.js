import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader }
       from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/index';
import { ChatsListItem } from './ChatsListItem';
import { ChatsListGroupingControl } from './ChatsListGroupingControl';

export class Nav extends React.Component {
  render() {
    const {lists, grouping, changeGrouping, toggleGroupingVisibility} = this.props;

    return (
      <NavFrame>
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
          <NavFrameHeader icon="fa-comments-o">Chat</NavFrameHeader>

          <SectionsPane>
            <Section>
              <SectionHeader>
                My Chats
                <div className="list-counter-bucket">
                  <a className="list-counter-dropdown active" href="#" onClick={toggleGroupingVisibility('my')}>
                    <span>&nbsp;</span>
                    <i className="fa fa-angle-down"></i>
                  </a>
                  <a className="list-counter active" href="#">{lists.my.total}</a>
                </div>
              </SectionHeader>

              <ul>
                {lists.my.items.map(item =>
                   <ChatsListItem groupBy={lists.my.groupBy} group={item.group} count={item.count} key={item.group} />)}
              </ul>
            </Section>

            <Section>
              <SectionHeader>
                All Chats
                <div className="list-counter-bucket">
                  <a className="list-counter-dropdown active" href="#" onClick={toggleGroupingVisibility('all')}>
                    <span>&nbsp;</span>
                    <i className="fa fa-angle-down"></i>
                  </a>
                  <a className="list-counter active" href="#">{lists.all.total}</a>
                </div>
              </SectionHeader>

              <ul>
                {lists.all.items.map(item =>
                   <ChatsListItem groupBy={lists.all.groupBy} group={item.group} count={item.count} key={item.group} />)}
              </ul>
            </Section>
          </SectionsPane>
        </div>
      </NavFrame>
    );
  }
}
