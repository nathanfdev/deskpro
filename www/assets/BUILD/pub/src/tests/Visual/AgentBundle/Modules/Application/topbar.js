import React from 'react';
import Isvg from 'react-inlinesvg';
import Immutable from 'immutable';
import { storiesOf, action } from '@storybook/react';
import User from 'DeskPRO/Bundle/AgentBundle/Modules/TopBar/Components/User';
import AddButton from 'DeskPRO/Bundle/AgentBundle/Modules/TopBar/Components/AddButton';
import Chat from 'DeskPRO/Bundle/AgentBundle/Modules/TopBar/Components/Chat';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { agents } from 'DemoState/AgentBundle/Modules/Application/topbar';
import teleOperator from '../../../Resources/teleoperator.jpg';
import { css } from '../../../decorators';

const newAgents = [];
agents.map(agent => newAgents.push(Immutable.Map(agent)));

storiesOf('App: top bar', module)
  .addDecorator(story => css(story()))
  .add(
    'Top bar',
    () => <div id="react_dp_agent_top_bar">
      <TopBar>
        <TopBarItem className="search-box legacy-omnibox">
          <SearchBox onUserInput={action('Search')} placeholder="Search ..." />
        </TopBarItem>
        <TopBarItem className="recent">
          <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/recent.svg`} />
        </TopBarItem>
        <AddButton />
        <TopBarRightMenu>
          <TopBarItem className="views">
            <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/views.svg`} />
          </TopBarItem>
          <TopBarItem>
            <TopBarNotificationIcon
              elementId="notifications"
              svg={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/notifications.svg`}
              count="2"
            />
          </TopBarItem>
          <TopBarItem>
            <User src={teleOperator} />
            <Chat
              agents={Immutable.fromJS(agents)}
              onlineAgents={['1', '8', '6']}
              updateVolume={action('Update volume')}
            />
          </TopBarItem>
        </TopBarRightMenu>
      </TopBar>
    </div>
  )
;
