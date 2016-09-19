import React from 'react';
import { storiesOf, action } from '@kadira/storybook'; // eslint-disable-line import/no-extraneous-dependencies
import { css } from 'Visual/decorators';
import { TopBar, TopBarItem } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { Container } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/ChatWindow';
import { IMOverlay, IMButton, TopBarRecentImList, GroupAddDrawer } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/TopBar';
import { imState, messages } from 'DemoState/AgentBundle/Modules/IM/im';
import Immutable from 'immutable';

const chatState = {
  ...imState,
  messages
};

storiesOf('Agent: IM', module)
  .addDecorator(story => css(story()))
  .add(
    'IMOverlay',
    () => <IMOverlay {...imState}>click here to open overlay</IMOverlay>
  )
  .add(
  'Agent: IM: TopBar intergrated with recent list',
  () => <TopBar>
    <div className="logo">
      <img src="/assets/BUILD/web/images/dp-logo-48.png" alt="DeskPRO logo" />
    </div>
    <SearchBox onUserInput={action('Search')} placeholder="Search ..." />
    <TopBarItem>
      <i className="icon wait" />
    </TopBarItem>
    <TopBarItem childrenWrapper="im-list">
      <TopBarRecentImList {...imState} onRecentClick={action('recent chat (top bar) click')}>
        <IMOverlay
          {...imState}
          onRecentClick={action('recent chat (list) click')}
          onParticipantClick={action('participant click')}
          createNewGroup={action('create new group')}
          toggleOverlay={action('toggle overlay')}
          isOpen
        >
          <IMButton />
        </IMOverlay>
      </TopBarRecentImList>
    </TopBarItem>
  </TopBar>)
  .add(
    'Agent: IM: Chat opened',
    () => <TopBar>
      <div className="logo">
        <img src="/assets/BUILD/web/images/dp-logo-48.png" alt="DeskPRO logo" />
      </div>
      <SearchBox onUserInput={action('Search')} placeholder="Search ..." />
      <TopBarItem>
        <i className="icon wait" />
      </TopBarItem>
      <TopBarItem childrenWrapper="im-list">
        <span>
          <TopBarRecentImList {...imState} onRecentClick={action('recent chat (top bar) click')}>
            <Container
              dispatch={() => console.log('www')}
              isOpen
              {...chatState}
              current={Immutable.fromJS({ id: '1', chat_type: 'agent', agents: [1, 6] })}
              onChange={action('reply')}
              onAttach={action('attach')}
              clickOut={action('clickout')}
            />
          </TopBarRecentImList>
        </span>
      </TopBarItem>
    </TopBar>
  )
  .add(
    'Agent: IM: Group creation',
    () => <TopBar>
      <div className="logo">
        <img src="/assets/BUILD/web/images/dp-logo-48.png" alt="DeskPRO logo" />
      </div>
      <SearchBox onUserInput={action('Search')} placeholder="Search ..." />
      <TopBarItem>
        <i className="icon wait" />
      </TopBarItem>
      <TopBarItem>
        <IMButton />
        <GroupAddDrawer
          isOpen
          target={document.getElementById('im-button')}
          {...imState}
          checkedAgents={{ 2: true, 6: true }}
          createGroup={action('creategroup')}
        />
      </TopBarItem>
    </TopBar>
  )
;
