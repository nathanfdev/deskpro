import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { css } from 'Visual/decorators';
import Container from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/ChatWindow/Container';
import { TopBar, TopBarItem } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import IMOverlay from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/TopBar/IMOverlay';
import IMButton from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/TopBar/IMButton';
import TopBarRecentImList from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/TopBar/TopBarRecentImList';
import { imState } from 'DemoState/AgentBundle/Modules/IM/im';

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
      <TopBarRecentImList {...imState}>
        <IMOverlay {...imState}>
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
        <TopBarRecentImList {...imState}>
          <Container isOpen {...imState} current={1}>123</Container>
        </TopBarRecentImList>
      </TopBarItem>
    </TopBar>
  )
;
