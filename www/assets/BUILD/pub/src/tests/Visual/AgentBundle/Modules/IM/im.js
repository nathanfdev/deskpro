import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import IMOverlay from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMOverlay';
import IMButton from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMButton';
import { css } from 'Visual/decorators';
import { TopBar, TopBarItem } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
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
  </TopBar>
)
;
