import React from 'react';
import Immutable from 'immutable';
import { storiesOf, action } from '@kadira/storybook';
import { css, redux } from 'tests/visual/decorators';
import { AppPane, NavPane, NavPaneContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { NavContainer as TicketsNav } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/NavContainer';
import { NavContainer as CrmNav } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/Nav/NavContainer';
import { NavContainer as ChatNav } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/Nav/NavContainer';
import { ticketsNavLoadingState, ticketsNavDemoState } from 'tests/DemoState/Navigation/tickets';
import { crmNavLoadingState, crmNavDemoState } from 'tests/DemoState/Navigation/crm';
import { chatNavLoadingState, chatNavDemoState } from 'tests/DemoState/Navigation/chat';

// ==================================================================
// TODO
// ==================================================================

// +++1. Move fakeTicketsState() etc to DemoState folder
// 2. Rename to upper case in autoload roots (E2E, Helpers, Unit etc)
// 3. Remove tests/ prefix from namespaces

// ==================================================================
// ==================================================================


/**
 * Decorate a concrete nav frame component with minimal context so that it's properly rendered
 */
function decorate(jsx, container = false) {
  const nav = container
    ? <NavPaneContainer>{jsx}</NavPaneContainer>
    : <NavPane isVisible={true}>{jsx}</NavPane>;

  return (
    <div id="deskpro_app_window">
      <div className="dp-window">
        <AppPane>
          {nav}
        </AppPane>
      </div>
    </div>
  );
}

/**
 * State indicating menu is collapsed (in hover mode)
 */
const collapsed = {Application: {dpWindow: {collapseNav: true}}};

function app(name, state) {
  return {Application: {dpWindow: {activeAppId: name}}, ...state};
}

/**
 * Navigation story book
 */
storiesOf('Navigation', module)
  .addDecorator(story => css(story()))

  // Expanded app nav frames

  .add('Expanded: Tickets', () => redux(app('tickets', ticketsNavDemoState), decorate(<TicketsNav />)))
  .add('Expanded: CRM', () => redux(app('crm', crmNavDemoState), decorate(<CrmNav />)))
  .add('Expanded: Chat', () => redux(app('chat', chatNavDemoState), decorate(<ChatNav />)))

  // Loading app nav frames

  .add('Loading: Tickets', () => redux(app('tickets', ticketsNavLoadingState), decorate(<TicketsNav />)))
  .add('Loading: CRM', () => redux(app('crm', crmNavLoadingState), decorate(<CrmNav />)))
  .add('Loading: Chat', () => redux(app('chat', chatNavLoadingState), decorate(<ChatNav />)))

  // Collapsed

  .add('Collapsed: Tickets', () => redux({...ticketsNavDemoState, ...collapsed}, decorate(<TicketsNav />, true)))
;
