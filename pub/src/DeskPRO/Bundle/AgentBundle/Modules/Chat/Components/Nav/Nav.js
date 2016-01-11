import React, {Component, PropTypes} from 'react';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { NavFrame, NavFrameHeader, NavFrameBody, SectionsPane }
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
    my: PropTypes.object.isRequired,
    all: PropTypes.object.isRequired,
    loaded: PropTypes.bool.isRequired,
    dpWindow: PropTypes.object.isRequired
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
    const {my, all, dpWindow, dispatch, loaded} = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-bubble-conversation-4">
          Chat
        </NavFrameHeader>
        <NavFrameBody>
          <LoadIndicator loaded={loaded}>
            <SectionsPane>
              <MyChats my={my}/>
              <AllChats all={all}/>
            </SectionsPane>
            <a href="#" onClick={this.demoNotifications}>Demo notifications</a>
          </LoadIndicator>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
