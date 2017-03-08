import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware, compose } from 'redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AgentBundle/DAL/config';
import { AgentTopBarContainer } from './Modules/TopBar/Components/AgentTopBar';
import { SideBarContainer } from './Modules/SideBar/Components/SideBar';
import { AgentList } from './Modules/Agent/Components/AgentList';
import { AgentOnboardingContainer }  from './Modules/Onboarding/Components/AgentOnboarding';
import { ArchiveFilesContainer } from './Modules/Tickets/Components/Archive/ArchiveFiles';
import VoiceControlsContainer from './Modules/Voice/Components/Controls/VoiceControlsContainer';
import VoiceTicketMessageContainer from './Modules/Voice/Components/TicketMessage/TicketMessageContainer';
import AgentReducers from './AgentApp_Reducers';
import AppReducers from '../AppBundle/AppApp_Reducers';
import { preloadData } from './Modules/Application/Actions/bootstrapActions';
import { setOnlineAgents, setOnlineUserChatAgents } from './Modules/Agent/Actions/agentActions';
import { NotificationServiceContainer } from './Modules/Application/Components/Notifications/NotificationServiceContainer';
import { isVoiceEnabledSelector } from './Modules/Voice/Selectors/client';
import { voiceBootstrap } from './Modules/Voice/Actions/clientActions';

class AgentLegacyApp {

  rendered = [];
  store;

  run() {
    // IE/Edge Hack http://stackoverflow.com/questions/1481251/what-does-document-domain-document-domain-do
    document.domain = document.domain;
    this.store = AgentLegacyApp.createStore();
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
      ReactDOM.render(<Provider store={this.store}>{element}</Provider>, node);
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
      node
    );
  }

  static createStore(initialState = {}) {
    // Bootstrap API and DAL
    setApi(api);
    loadRepositoriesConfig(repositoriesConfig);

    // This builder calls compile on old-style reducers
    // created via the Reducer class
    const legacyReducerBuilder = (reducer) => {
      if (reducer.isAmplifluxReducer) {
        const rInst = new reducer();
        return rInst.compile();
      }

      return reducer;
    };

    const reducer = combineReducerHierarchy(Object.assign({}, AgentReducers, AppReducers), legacyReducerBuilder);
    const middleware = applyMiddleware(
      ampMiddleware.timerMiddleware('startTime'),
      ampMiddleware.intervalMiddleware,
      ampMiddleware.timeoutMiddleware,
      ampMiddleware.actionThunkMiddleware,
      ampMiddleware.redispatchDsaPayload,
      ampMiddleware.guidMiddleware,
      ampMiddleware.promiseMiddleware,
      ampMiddleware.loggerMiddleware
    );
    const makeStore = compose(middleware)(createStore);

    return makeStore(reducer, initialState, window.devToolsExtension ? window.devToolsExtension() : f => f);
  }
}
export default AgentLegacyApp;
