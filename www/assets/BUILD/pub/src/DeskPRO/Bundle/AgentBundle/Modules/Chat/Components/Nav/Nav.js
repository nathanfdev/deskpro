import React, { Component, PropTypes } from 'react';
import {
  NavFrame, NavFrameHeaderContainer, NavFrameBody, SectionsPane
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { MyChats } from './Sections/MyChats';
import { AllChats } from './Sections/AllChats';
import { pureRender } from 'Ampliflux';

// @todo Remove
import {
  infoNotification, errorNotification, delayedActionNotification, undoableActionNotification
}
  from '../../../Application/Actions/notificationActions';

@pureRender
export class Nav extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    my:       PropTypes.object.isRequired,
    all:      PropTypes.object.isRequired,
    isLoaded: PropTypes.bool.isRequired
  };

  // @todo Remove
  demoNotifications = () => {
    const { dispatch } = this.props;
    dispatch(delayedActionNotification({
                                         alive:  25,
                                         title:  'Are you sure you want to send 42 emails to your colleagues?',
                                         text:   'This one has custom alive period of 25 sec',
                                         commit: () => alert('Committing the action: sending emails'),
                                         undo:   () => alert(
                                           '"undo" callback for delayedActionNotification is optional, usually there will be nothing to undo because ' +
                                           'changes are not committed, yet we may want to redirect somewhere on undo or something like that.'
                                         )
                                       }));
    dispatch(undoableActionNotification({
                                          title: 'You\'ve gone invisible',
                                          text:  'No one will know you are online',
                                          undo:  () => alert('Undoing: dispatching actions to reach the previous state')
                                        }));
    dispatch(infoNotification({
                                title: 'Your profile information has been updated'
                              }));
    dispatch(errorNotification({
                                 title: 'Cannot retrieve the attachment, server responded with error',
                                 text:  'Sorry for the inconvenience, we\'re notified about the problem and will fix it ASAP. Please try again later.'
                               }));
  };

  render() {
    const { my, all, isLoaded } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-bubble-conversation-4">Chat</NavFrameHeaderContainer>
        <NavFrameBody isLoaded={isLoaded}>
          <SectionsPane>
            <MyChats my={my} />
            <AllChats all={all} />
          </SectionsPane>
          <a href="#" onClick={this.demoNotifications}>Demo notifications</a>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
