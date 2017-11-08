import React from 'react';
import { storiesOf, action } from '@kadira/storybook'; // eslint-disable-line import/no-extraneous-dependencies
import { List } from 'immutable';
import { SnippetsMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsMenu';
import { SnippetsModal } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsModal';
import {
  snippetsState,
  editSnippet,
  editTranslation,
  languages,
  me,
  agentTeams,
  ticketDepartments,
  chatDepartments
} from 'DemoState/AgentBundle/Modules/Snippets/snippets';
import { OwnershipSelect } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/Menus/OwnershipSelect';
import { css } from '../../../decorators';

window.DeskPRO_Window = {
  keyboardShortcuts: {
    isPaused: true
  }
};

window.DESKPRO_PERSON_PERMS = {
  'agent_snippets.delete_by_others': true
};

storiesOf('Agent: Snippets', module)
  .addDecorator(story => css(story()))
  .add(
    'Left menu',
    () => <SnippetsMenu
      snippets={snippetsState}
      langId={1}
      me={me}
      langDisplay={[1, 2, 3]}
      langPref={[1, 2, 3]}
      width={700}
      languages={languages}
    />
  )
  .add(
    'Modal',
    () => <SnippetsModal
      me={me}
      snippet={editSnippet}
      translation={editTranslation}
      languages={languages}
      langId={1}
      ticketCustomFields={new List()}
      personCustomFields={new List()}
      userChatCustomFields={new List()}
      agentTeams={agentTeams}
      ticketDepartments={ticketDepartments}
      chatDepartments={chatDepartments}
      types={['ticket']}
      snippetTeams={[]}
      snippetDepartments={[]}
      labels={[]}
    />
  )
  .add(
    'Ownership Select',
    () => <OwnershipSelect
      agentTeams={agentTeams}
      selectedTeams={[2]}
      onChange={action('Change')}
    />
  )
;
