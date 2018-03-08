import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { AppContainer } from 'react-hot-loader';
import { DragDropContextProvider } from 'react-dnd';
import HTML5Backend from 'react-dnd-html5-backend';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { AgentTopBarContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/TopBar/Components/AgentTopBar';
import { SideBarContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/SideBar/Components/SideBar';
import { LeftDrawerContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/SideBar/Components/LeftDrawer';
import { AgentList } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Components/AgentList';
import { AgentOnboardingContainer }  from 'DeskPRO/Bundle/AgentBundle/Modules/Onboarding/Components/AgentOnboarding';
import { ArchiveFilesContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Archive/ArchiveFiles';
import { GuideTreeContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/Components/List/GuideTree';
import { EditorContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/Components/Editor/Editor';
import VoiceControlsContainer from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Components/Controls/VoiceControlsContainer';
import VoiceTicketMessageContainer from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Components/TicketMessage/TicketMessageContainer';
import { preloadData } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/bootstrapActions';
import { setOnlineAgents, setOnlineUserChatAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/agentActions';
import { NotificationServiceContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/Notifications/NotificationServiceContainer';
import { isVoiceEnabledSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Selectors/client';
import { outboundNumbersSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Selectors/numbers';
import { voiceBootstrap, openDialpad } from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Actions/clientActions';
import DeskproAppStore from 'DeskPRO/Bundle/AgentBundle/Modules/DeskproApps/DeskproAppStore';
import LegacyStoreProvider from 'DeskPRO/Bundle/AgentBundle/Services/LegacyStoreProvider';
import LegacySnippetInserter from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Services/LegacySnippetInserter';
import RteTextArea from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/Services/RteTextarea';
import store from 'DeskPRO/Bundle/AgentBundle/Services/store';
import { FollowUpContainer } from './Modules/Tickets/Components/FollowUp/FollowUp';


class AgentLegacyApp {

  rendered = {};
  renderWaits = {};
  store;

  run() {
    // IE/Edge Hack http://stackoverflow.com/questions/1481251/what-does-document-domain-document-domain-do
    document.domain = document.domain;

    window.LegacyRteTextarea = new RteTextArea();
    window.LegacySnippetInserter = new LegacySnippetInserter();

    if (window.DP_SKIP_REACT) {
      return;
    }
    this.store = store;
    // let the app store know we started the run sequence, so it can register any globals, etc
    DeskproAppStore.onAgentLegacyAppRun(store, window);

    this.store.dispatch(preloadData()).then(() => {
      window.$(document).ready(() => this.start());
    });
  }

  start() {
    window.DP_DEV_MODE = __DEV__; // eslint-disable-line no-undef
    if (typeof window.DeskPRO_Window === 'undefined'
      || !this.store.getState().Application.bootstrap.get('isBootstrapped')) {
      setTimeout(this.start.bind(this), 100);
    } else {
      this.renderPiece(AgentTopBarContainer, AgentTopBarContainer.getType());
      this.renderPiece(AgentList, AgentList.getType());
      this.renderPiece(SideBarContainer, SideBarContainer.getType());
      this.renderPiece(LeftDrawerContainer, LeftDrawerContainer.getType());
      this.renderPiece(AgentOnboardingContainer, AgentOnboardingContainer.getType());
      this.renderPiece(NotificationServiceContainer, NotificationServiceContainer.getType());
      window.$('#dp_loading').remove();

      window.LegacyStoreProvider = new LegacyStoreProvider();
      window.LegacyStoreProvider.init(this.store);

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

     // let the app store know we finished the start sequence so
      DeskproAppStore.onAgentLegacyAppReady(this.store, window, api, messageBroker);
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
      clearTimeout(this.renderWaits[piecePlace]);
      if (piecePlace === 'AgentTopBar') {
        ReactDOM.render(
          <AppContainer>
            <Provider store={this.store}>
              <DragDropContextProvider backend={HTML5Backend} window={node}>
                {element}
              </DragDropContextProvider>
            </Provider>
          </AppContainer>, node);
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
    this.renderWaits[piecePlace] = setTimeout(() => this.renderPiece(piece, piecePlace), 1000);
  }

  renderVoiceControls(node, ticketId, onEndCall) {
    let tabRef;

    ReactDOM.render(
      <AppContainer>
        <Provider store={this.store}>
          <VoiceControlsContainer
            tabRef={(c) => { tabRef = c; }}
            ticketId={ticketId}
            onEndCall={onEndCall}
          />
        </Provider>
      </AppContainer>,
      node
    );

    return tabRef;
  }

  canOpenDialpad() {
    const state = this.store.getState();
    const outboundNumbers = outboundNumbersSelector(state);

    return outboundNumbers.size > 0;
  }

  openVoiceDialpad(number) {
    this.store.dispatch(openDialpad(number));
  }

  unmountVoiceControls(node) { // eslint-disable-line
    ReactDOM.unmountComponentAtNode(node);
  }

  renderVoiceMessage(node, data) {
    let tabRef;

    ReactDOM.render(
      <AppContainer>
        <Provider store={this.store}>
          <VoiceTicketMessageContainer
            tabRef={(c) => { tabRef = c; }}
            data={data}
          />
        </Provider>
      </AppContainer>,
      node
    );

    return tabRef;
  }

  renderMessageArchiveAttachment(node, data, layout) {
    ReactDOM.render(
      <AppContainer>
        <Provider store={this.store}>
          <ArchiveFilesContainer
            authId={data.authId}
            layout={layout}
          />
        </Provider>
      </AppContainer>,
      node.get(0)
    );
  }

  renderFollowUpTab(node, data) {
    ReactDOM.render(
      <AppContainer>
        <Provider store={this.store}>
          <FollowUpContainer
            {...data}
          />
        </Provider>
      </AppContainer>,
      node
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
      <AppContainer>
        <Provider store={this.store}>
          <EditorContainer
            value={value}
            inputType={inputType}
            save={save}
            updateSource={updateSource}
          />
        </Provider>
      </AppContainer>,
      node
    );
  }

  renderTopicsTree(node, guideId, height, openTopic, displayStatuses) {
    ReactDOM.render(
      <AppContainer>
        <Provider store={this.store}>
          <GuideTreeContainer
            guideId={guideId}
            height={height}
            openTopic={openTopic}
            displayStatuses={displayStatuses}
          />
        </Provider>
      </AppContainer>,
      node
    );
  }
}

if (module.hot) {
  module.hot.accept();
}

export default AgentLegacyApp;
