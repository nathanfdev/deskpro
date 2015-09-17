import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, ListGroupingControl }
       from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class Nav extends React.Component {
  render() {
    const {lists, grouping, changeGrouping, toggleGroupingVisibility, onMyClick, onAllClick} = this.props;

    return (
      <NavFrame>
        <div part="outer">

          <ListGroupingControl
            title="My Chats"
            options={grouping.my.options}
            visible={grouping.my.visible}
            onChange={changeGrouping('my')}
          />

          <ListGroupingControl
            title="All Chats"
            options={grouping.all.options}
            visible={grouping.all.visible}
            onChange={changeGrouping('all')}
          />

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
                   <ListItemContainer
                     groupBy={lists.my.groupBy}
                     group={item.group}
                     count={item.count}
                     key={item.group}
                     onClick={onMyClick}
                   />
                )}
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
                   <ListItemContainer
                     groupBy={lists.all.groupBy}
                     group={item.group}
                     count={item.count}
                     key={item.group}
                     onClick={onAllClick}
                   />
                )}
              </ul>
            </Section>
          </SectionsPane>
        </div>
      </NavFrame>
    );
  }
}
