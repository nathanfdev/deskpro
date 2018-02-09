import React from 'react';
import Isvg from 'react-inlinesvg';
import { storiesOf, action } from '@storybook/react'; // eslint-disable-line import/no-extraneous-dependencies
import { css } from 'Visual/decorators';
import { TopBar, TopBarItem } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { Container } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/ChatWindow';
import AddButton from 'DeskPRO/Bundle/AgentBundle/Modules/TopBar/Components/AddButton';
import { IMOverlay, IMButton, TopBarRecentImList, GroupAddDrawer } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/TopBar';
import { imState, messages } from 'DemoState/AgentBundle/Modules/IM/im';
import recentSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/topbar/recent.svg';
import Immutable from 'immutable';

const chatState = {
  ...imState,
  messages
};

storiesOf('Agent: IM', module)
  .addDecorator(story => css(story()))
  .add(
  'Agent: IM: TopBar intergrated with recent list',
  () =>
    <div id="react_dp_agent_top_bar">
      <TopBar>
        <TopBarItem classes={['search-box legacy-omnibox']}>
          <SearchBox onUserInput={action('Search')} placeholder="Search ..." />
        </TopBarItem>
        <TopBarItem classes={['recent']}>
          <Isvg src={recentSvg} />
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
        <AddButton />
      </TopBar>
    </div>
  )
  .add(
    'Agent: IM: Chat opened',
    () =>
      <div id="react_dp_agent_top_bar">
        <TopBar>
          <TopBarItem classes={['search-box legacy-omnibox']}>
            <SearchBox onUserInput={action('Search')} placeholder="Search ..." />
          </TopBarItem>
          <TopBarItem classes={['recent']}>
            <Isvg src={recentSvg} />
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
                  markNewMessages={action('mark messages')}
                />
              </TopBarRecentImList>
            </span>
          </TopBarItem>
          <AddButton />
        </TopBar>
      </div>
  )
  .add(
    'Agent: IM: Group creation',
    () =>
      <div id="react_dp_agent_top_bar">
        <TopBar>
          <TopBarItem classes={['search-box legacy-omnibox']}>
            <SearchBox onUserInput={action('Search')} placeholder="Search ..." />
          </TopBarItem>
          <TopBarItem classes={['recent']}>
            <Isvg src={recentSvg} />
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
      </div>
  )
;
