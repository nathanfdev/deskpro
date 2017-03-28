import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { DragDropContextProvider } from 'react-dnd';
import HTML5Backend from 'react-dnd-html5-backend';
import { AgentTopBarContainer } from './Modules/TopBar/Components/AgentTopBar';
import { SideBarContainer } from './Modules/SideBar/Components/SideBar';
import { AgentList } from './Modules/Agent/Components/AgentList';
import { AgentOnboardingContainer }  from './Modules/Onboarding/Components/AgentOnboarding';
import { ArchiveFilesContainer } from './Modules/Tickets/Components/Archive/ArchiveFiles';
import { GuideTreeContainer } from './Modules/Publish/Components/List/GuideTree';
import { EditorContainer } from './Modules/Publish/Components/Editor/Editor';
import VoiceControlsContainer from './Modules/Voice/Components/Controls/VoiceControlsContainer';
import VoiceTicketMessageContainer from './Modules/Voice/Components/TicketMessage/TicketMessageContainer';
import { preloadData } from './Modules/Application/Actions/bootstrapActions';
import { setOnlineAgents, setOnlineUserChatAgents } from './Modules/Agent/Actions/agentActions';
import { NotificationServiceContainer } from './Modules/Application/Components/Notifications/NotificationServiceContainer';
import { isVoiceEnabledSelector } from './Modules/Voice/Selectors/client';
import { voiceBootstrap } from './Modules/Voice/Actions/clientActions';
import store from './Services/store';

class AgentLegacyApp {

  rendered = [];
  store;

  run() {
    // IE/Edge Hack http://stackoverflow.com/questions/1481251/what-does-document-domain-document-domain-do
    document.domain = document.domain;
    this.store = store;
    this.store.dispatch(preloadData()).then(() => {
      window.$(document).ready(() => this.start());
    });
  }

  start() {
    // window.DP_DEV_MODE = __DEV__; // eslint-disable-line no-undef
    if (typeof window.DeskPRO_Window === 'undefined'
      || !this.store.getState().Application.bootstrap.get('isBootstrapped')) {
      setTimeout(this.start.bind(this), 100);
    } else {
      this.renderPiece(AgentTopBarContainer, AgentTopBarContainer.getType());
      this.renderPiece(AgentList, AgentList.getType());
      this.renderPiece(SideBarContainer, SideBarContainer.getType());
      this.renderPiece(AgentOnboardingContainer, AgentOnboardingContainer.getType());
      if (window.DP_HAS_NEW_IM) {
        this.renderPiece(NotificationServiceContainer, NotificationServiceContainer.getType());
      }
      window.$('#dp_loading').remove();

      const messageBroker = window.DeskPRO_Window.getMessageBroker();
      messageBroker.addMessageListener('agent.online-agents', (event) => {
        this.store.dispatch(setOnlineAgents(event.online_agents));
      });
      messageBroker.addMessageListener('agent.online-agents-userchat', (event) => {
        this.store.dispatch(setOnlineUserChatAgents(event.online_agents));
      });

      if (window.DP_HAS_VOICE) {
        const state = this.store.getState();
        const voiceEnabled = isVoiceEnabledSelector(state);

        if (voiceEnabled) {
          this.store.dispatch(voiceBootstrap());
        }
      }
    }
  }

  renderPiece(piece, piecePlace) {
    if (this.rendered[piecePlace]) {
      return;
    }

    const element = React.createElement(piece, { store: this.store });
    const elementPlace = piecePlace.replace(/([A-Z])/g, $1 => `_${$1.toLowerCase()}`);
    const node = document.getElementById(`react_dp${elementPlace}`);

    if (node) {
      this.rendered[piecePlace] = true;
      if (piecePlace === 'AgentTopBar') {
        ReactDOM.render(
          <Provider store={this.store}>
            <DragDropContextProvider backend={HTML5Backend} window={node}>
              {element}
            </DragDropContextProvider>
          </Provider>, node);
      } else {
        ReactDOM.render(<Provider store={this.store}>{element}</Provider>, node);
      }
      return;
    }
    // this is just a stub. Possibly we just need some event listener or so to handle element rendering when
    // some page component was just loaded.
    // maybe we could use something like angular directive or so.
    // it's not good when there are elements for different pages.
    // Or, we may just call this method when it's needed.
    setTimeout(() => this.renderPiece(piece, piecePlace), 1000);
  }

  renderVoiceControls(node, ticketId, onEndCall) {
    let tabRef;

    ReactDOM.render(
      <Provider store={this.store}>
        <VoiceControlsContainer
          tabRef={(c) => { tabRef = c; }}
          ticketId={ticketId}
          onEndCall={onEndCall}
        />
      </Provider>,
      node
    );

    return tabRef;
  }

  unmountVoiceControls(node) { // eslint-disable-line
    ReactDOM.unmountComponentAtNode(node);
  }

  renderVoiceMessage(node, data) {
    let tabRef;

    ReactDOM.render(
      <Provider store={this.store}>
        <VoiceTicketMessageContainer
          tabRef={(c) => { tabRef = c; }}
          data={data}
        />
      </Provider>,
      node
    );

    return tabRef;
  }

  renderMessageArchiveAttachment(node, data) {
    ReactDOM.render(
      <Provider store={this.store}>
        <ArchiveFilesContainer
          authId={data.authId}
        />
      </Provider>,
      node.get(0)
    );
  }

  renderContentEditor(
    node,
    value,
    inputType,
    save,
    updateSource
  ) {
    ReactDOM.render(
      <Provider store={this.store}>
        <EditorContainer
          value={value}
          inputType={inputType}
          save={save}
          updateSource={updateSource}
        />
      </Provider>,
      node
    );
  }

  renderTopicsTree(node, guideId, height, openTopic) {
    ReactDOM.render(
      <Provider store={this.store}>
        <GuideTreeContainer
          guideId={guideId}
          height={height}
          openTopic={openTopic}
        />
      </Provider>,
      node
    );
  }
}
export default AgentLegacyApp;
