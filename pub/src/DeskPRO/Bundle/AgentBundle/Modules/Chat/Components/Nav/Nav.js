import React from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, SectionsPane, Section, SectionHeader, ListGroupingControl }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';
import { pureRender } from 'Ampliflux';

// @todo Remove
import { infoNotification, errorNotification, delayedActionNotification, undoableActionNotification }
  from '../../../Application/Actions/notificationActions';

@pureRender
export class Nav extends React.Component {

  static groupingOptions = {
    my: [
      { value: 'date_period', label: 'Date Created' },
      { value: 'department', label: 'Department' }
    ],
    all: [
      { value: 'agent', label: 'Agent' },
      { value: 'department', label: 'Department' },
      { value: 'date_period', label: 'Date Created' }
    ]
  };

  // @todo Remove
  demoNotifications = () => {
    this.props.dispatch(delayedActionNotification({
      alive: 25,
      title: 'Are you sure you want to send 42 emails to your colleagues?',
      text: 'This one has custom alive period of 25 sec',
      commit: () => alert('Committing the action: sending emails'),
      undo: () => alert(
        '"undo" callback for delayedActionNotification is optional, usually there will be nothing to undo because ' +
        'changes are not committed, yet we may want to redirect somewhere on undo or something like that.'
      )
    }));
    this.props.dispatch(undoableActionNotification({
      title: 'You\'ve gone invisible',
      text: 'No one will know you are online',
      undo: () => alert('Undoing: dispatching actions to reach the previous state')
    }));
    this.props.dispatch(infoNotification({
      title: 'Your profile information has been updated'
    }));
    this.props.dispatch(errorNotification({
      title: 'Cannot retrieve the attachment, server responded with error',
      text: 'Sorry for the inconvenience, we\'re notified about the problem and will fix it ASAP. Please try again later.'
    }));
  };

  render() {
    const {lists, changeGrouping, toggleGroupingVisibility, onMyClick, onAllClick, dpWindow, dispatch} = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <div part="outer">

          <ListGroupingControl title="My Chats"
                               options={Nav.groupingOptions.my}
                               visible={lists.getIn(['my', 'isGroupingControlVisible'])}
                               onChange={changeGrouping('my')}
                               attachTo={this.refs.mySection}
                               close={toggleGroupingVisibility('my')}/>

          <ListGroupingControl title="All Chats"
                               options={Nav.groupingOptions.all}
                               visible={lists.getIn(['all', 'isGroupingControlVisible'])}
                               onChange={changeGrouping('all')}
                               attachTo={this.refs.allSection}
                               close={toggleGroupingVisibility('all')}/>

        </div>

        <div part="inner">
          <NavFrameHeader icon="icon-dp-streamline-bubble-conversation-4">
            Chat
          </NavFrameHeader>
          <NavFrameBody>
            <SectionsPane>
              <Section ref="mySection">
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
                      <ListItemContainer groupBy={lists.getIn(['my', 'groupBy'])}
                                         group={item.group}
                                         count={item.count}
                                         key={item.group}
                                         onClick={onMyClick}/>
                  )}
                </ul>
              </Section>

              <Section ref="allSection">
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
                      <ListItemContainer groupBy={lists.getIn(['all', 'groupBy'])}
                                         group={item.group}
                                         count={item.count}
                                         key={item.group}
                                         onClick={onAllClick}/>
                  )}
                </ul>
              </Section>
            </SectionsPane>

            <br /><br /><br />
            <a href="#" onClick={this.demoNotifications}>Demo notifications</a>
          </NavFrameBody>
        </div>
      </NavFrame>
    );
  }
}
