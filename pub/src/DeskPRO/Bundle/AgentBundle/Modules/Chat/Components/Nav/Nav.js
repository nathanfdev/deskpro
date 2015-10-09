import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, ListGroupingControl }
       from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';
import { pureRender } from 'Ampliflux';

@pureRender
export class Nav extends React.Component {

  static groupingOptions = {
    my: [
      {value: 'date_period', label: 'Date Created'},
      {value: 'department', label: 'Department'}
    ],
    all: [
      {value: 'agent', label: 'Agent'},
      {value: 'department', label: 'Department'},
      {value: 'date_period', label: 'Date Created'}
    ]
  };

  render() {
    const {lists, changeGrouping, toggleGroupingVisibility, onMyClick, onAllClick, dpWindow, dispatch} = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <div part="outer">

          <ListGroupingControl
            title="My Chats"
            options={Nav.groupingOptions.my}
            visible={lists.getIn(['my', 'isGroupingControlVisible'])}
            onChange={changeGrouping('my')}
          />

          <ListGroupingControl
            title="All Chats"
            options={Nav.groupingOptions.all}
            visible={lists.getIn(['all', 'isGroupingControlVisible'])}
            onChange={changeGrouping('all')}
          />

        </div>

        <div part="inner">
          <NavFrameHeader icon="icon-dp-streamline-bubble-conversation-4">
            Chat
          </NavFrameHeader>

          <SectionsPane>
            <Section>
              <SectionHeader>
                My Chats
                <div className="list-counter-bucket">
                  <a className="list-counter-dropdown active" href="#" onClick={toggleGroupingVisibility('my')}>
                    <span>&nbsp;</span>
                    <i className="fa fa-angle-down"></i>
                  </a>
                  <a className="list-counter active" href="#">{lists.getIn(['my', 'total'])}</a>
                </div>
              </SectionHeader>

              <ul>
                {lists.getIn(['my', 'items']).map(item =>
                   <ListItemContainer
                     groupBy={lists.getIn(['my', 'groupBy'])}
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
                  <a className="list-counter active" href="#">{lists.getIn(['all', 'total'])}</a>
                </div>
              </SectionHeader>

              <ul>
                {lists.getIn(['all', 'items']).map(item =>
                   <ListItemContainer
                     groupBy={lists.getIn(['all', 'groupBy'])}
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
