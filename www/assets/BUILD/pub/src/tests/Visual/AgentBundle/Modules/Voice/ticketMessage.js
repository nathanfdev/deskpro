import React from 'react';
import { storiesOf, action } from '@storybook/react';
import TicketMessageContainer from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Components/TicketMessage/TicketMessageContainer';
import { css, redux } from '../../../decorators';
import demoState from './demoState';

storiesOf('Agent Voice', module)
  .addDecorator(story => css(story()))
  .add(
    'Ticket message',
    () => redux(demoState, (
      <TicketMessageContainer
        data={{
          data: {
            id:         10,
            attributes: [{ phone_call: 1 }]
          },
          linked: {
            voice_phone_call: {
              1: {
                from_number:     '02397 785 432',
                phone_call_logs: [
                  {
                    action_type: 'call.new_incoming'
                  },
                  {
                    action_type: 'call.user_joined'
                  },
                  {
                    action_type: 'call.answered'
                  },
                  {
                    action_type: 'call.started'
                  },
                  {
                    action_type: 'call.agent_joined'
                  }
                ],
                participants: [{ person: '1' }, { person: '2' }]
              }
            }
          }
        }}
        transcript="What I am trying to say is that the appeal and the settlement are separate.You mean that communications and announcements internal and external would be brought into the appeal process. Right,
        because they connect to the appeal. I hope you understand there are issues of reputation, and I have to deal with those in the appeal process rather than the commercial issues around settlement. And
        then, there is this legal claim for defamation. I believe that the implication would be to perceive in for defamation. Yes. I will investigate to what we have as written evidence and what we do not have. Okay.
        Great! I am thankful for you to do that. bsolutely. Evidences are important in order to understand whether the party in question has acted appropriately through this process, and there is greater possibility
        of defamation to everybody in circumstance like this. Exactly. I hope you feel that you have been listened to. Yes, absolutely. Thank you. What I have also noted down from this is that there are a number of
        other people that I need to speak to which I will do as quickly as I possibly can. You have also raised through these conversations a number of points, which I now need to go back and test from the other
        perspective to try and make my judgment about working of the evidence side. I mean strictly because it is a lot of confidential and personal stuff, and the effect on my team is also something that is sort of
        there on my mind which is why at the very start of the whole process I thought this could be dealt with very very quickly and very very smoothly or it could be just a traumatic long drawn out process. Well, I
        appreciate the fact that you have of course your personal interests. I am convinced that you have an intention to make this work if at all possible, and the way it makes sense to the other party too. So, I
        appreciate that. Well, the truth is I wouldn’t come to an appeal if there wasn’t a genuine interest in helping you understand. Okay, Justin. Thank you for struggling in so far. What else would you want to
        cover? Nothing really. I believe it’s not the right stage for you to talk about the timeline. Maybe it’s going to be quicker than we originally thought. But I am just worried about any backfilling. But let me know
        if you could work out the timeline at some point. I think I would go back to Emma, Danny, and Bud to understand the evidence we spokeabout and how that trading thing got so serious. Written evidences
        would be important; however, I will be challenging them with many of the points we have made in this conversation. Alright, sounds cool Okay .Thank you."
        onCall={action('onCall')}
        onPlay={action('onPlay')}
        onMove={action('onMove')}
        onStepBackward={action('onStepBackward')}
        onOpenSettings={action('onOpenSettings')}
      />
    ))
  )
;
