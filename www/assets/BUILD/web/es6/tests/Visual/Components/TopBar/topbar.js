import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon, User, Chat } from 'Components/TopBar';
import SearchBox from 'Components/SearchBox';

storiesOf('App: top bar', module)
  .add(
    'Top bar',
    () => <TopBar>
         <SearchBox onUserInput={action('SearchInput')} placeholder="Search"/>
         <TopBarItem onClick={action('MenuClick')}>
           <i className="icon wait"/>
         </TopBarItem>
         <TopBarRightMenu>
           <TopBarItem onClick={action('MenuClick')}>
             <TopBarNotificationIcon icon="bell" count="2"/>
           </TopBarItem>
           <TopBarItem>
            <User src="es6/tests/Visual/Resources/teleoperator.jpg"/>
            <Chat onlineAgents={agents} />
          </TopBarItem>
        </TopBarRightMenu>
      </TopBar>
  )
;
const agents = [
  {
    name: 'Julien Ducro',
    department: 'Support'
  }
];