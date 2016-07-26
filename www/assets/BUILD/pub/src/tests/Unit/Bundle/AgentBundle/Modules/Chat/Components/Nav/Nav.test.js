// #define ~root DeskPRO/Bundle/AgentBundle/Modules/Chat
// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/Nav

jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/frame');
jest.dontMock('~nav/Nav');

import React from 'react';
import ReactDOM from 'react-dom';
import { toImmutable } from 'Helpers';
import { renderInChatApp } from '../../chat.test-helper';
import { chatNavDemoState } from 'DemoState/Navigation/chat';

describe('Chat Navigation: Nav component', () => {
  const Nav = require('~nav/Nav').Nav;
  const LoadIndicator = require('DeskPRO/Component/LoadIndicator').LoadIndicator;

  const counts = toImmutable(chatNavDemoState.Chat.nav.counts);

  let node;

  function render(isLoaded = true) {
    node = ReactDOM.findDOMNode(renderInChatApp({}, <Nav
      counts={counts}
      isLoaded={isLoaded}
      changeCountGroupingCallbackFactory={() => window.void}
    />));
  }

  it("should render a spinner while data aren't loaded", () => {
    spyOn(LoadIndicator.prototype, 'render').and.callThrough();
    render(false);
    expect(LoadIndicator.prototype.render).toHaveBeenCalled();
  });

  it('should render "Chat" header', () => {
    render();

    const h1 = node.querySelector('h1');
    expect(h1.textContent).toEqual('Chat');
  });

  it('should render "My Chats" as the first section', () => {
    render();

    const titles = node.querySelectorAll('.list-sidebar-title');
    expect(titles[0].textContent.toLowerCase()).toContain('my chats');
  });

  it('should render "All Chats" as the second section', () => {
    render();

    const titles = node.querySelectorAll('.list-sidebar-title');
    expect(titles[1].textContent.toLowerCase()).toContain('all chats');
  });
});
