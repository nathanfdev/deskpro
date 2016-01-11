import React, {Component, PropTypes} from 'react';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { NavFrame, NavFrameHeader, NavFrameBody, SectionsPane, Section, SectionHeader, ListGroupingControl }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { MyChats } from './Sections/MyChats';
import { AllChats } from './Sections/AllChats';
import { pureRender } from 'Ampliflux';

// @todo Remove
import { infoNotification, errorNotification, delayedActionNotification, undoableActionNotification }
  from '../../../Application/Actions/notificationActions';

@pureRender
export class Nav extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    changeGrouping: PropTypes.func.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    onMyClick: PropTypes.func.isRequired,
    onAllClick: PropTypes.func.isRequired,
    my: PropTypes.object.isRequired,
    all: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

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
    const {my, all, labels, changeGrouping, toggleGroupingVisibility, dpWindow, dispatch, loaded} = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <div part="outer">

          <ListGroupingControl title="My Chats"
                               options={Nav.groupingOptions.my}
                               visible={my.get('isGroupingControlVisible')}
                               onChange={changeGrouping('my')}
                               attachTo={this.refs.mySection}
                               close={toggleGroupingVisibility('my')}/>

          <ListGroupingControl title="All Chats"
                               options={Nav.groupingOptions.all}
                               visible={all.get('isGroupingControlVisible')}
                               onChange={changeGrouping('all')}
                               attachTo={this.refs.allSection}
                               close={toggleGroupingVisibility('all')}/>

        </div>

        <div part="inner">
          <NavFrameHeader icon="icon-dp-streamline-bubble-conversation-4">
            Chat
          </NavFrameHeader>
          <NavFrameBody>
            <LoadIndicator loaded={loaded}>
              <SectionsPane>
                <MyChats my={my}
                         labels={labels}/>
                <AllChats all={all}
                          labels={labels}/>
              </SectionsPane>
              <a href="#" onClick={this.demoNotifications}>Demo notifications</a>
            </LoadIndicator>
          </NavFrameBody>
        </div>
      </NavFrame>
    );
  }
}
