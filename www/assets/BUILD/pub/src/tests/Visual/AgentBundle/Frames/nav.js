import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { AppPane, NavPane, NavPaneContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { NavContainer as CrmNav } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/Nav/NavContainer';
import { NavContainer as ChatNav } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/Nav/NavContainer';
import { css, redux } from '../../decorators';
import { ticketsNavLoadingState, ticketsNavDemoState } from '../../../DemoState/Navigation/tickets';
import { crmNavLoadingState, crmNavDemoState } from '../../../DemoState/Navigation/crm';
import { chatNavLoadingState, chatNavDemoState } from '../../../DemoState/Navigation/chat';

/**
 * Decorate a concrete nav frame component with minimal context so that it's properly rendered
 */
function decorate(jsx, container = false) {
  const nav = container
    ? <NavPaneContainer>{jsx}</NavPaneContainer>
    : <NavPane isVisible>{jsx}</NavPane>;

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
const collapsed = { Application: { dpWindow: { collapseNav: true } } };

function app(name, state) {
  return { Application: { dpWindow: { activeAppId: name } }, ...state };
}

/**
 * Navigation story book
 */
storiesOf('App: Navigation', module)
  .addDecorator(story => css(story()))

  // Expanded app nav frames

  .add('Expanded: CRM', () => redux(app('crm', crmNavDemoState), decorate(<CrmNav />)))
  .add('Expanded: Chat', () => redux(app('chat', chatNavDemoState), decorate(<ChatNav />)))

  // Loading app nav frames

  .add('Loading: CRM', () => redux(app('crm', crmNavLoadingState), decorate(<CrmNav />)))
  .add('Loading: Chat', () => redux(app('chat', chatNavLoadingState), decorate(<ChatNav />)))
;
