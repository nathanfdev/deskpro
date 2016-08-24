import React from 'react';
import { storiesOf } from '@kadira/storybook';
import IMOverlay from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMOverlay';
import { css } from 'Visual/decorators';
import Immutable from 'immutable';

const agentOne = {
  id:           2,
  name:         'Robert Baratheon, Lord of Seven Kingdoms, Protector of Realm, King of the Andals and the First Men',
  first_name:   'Robert',
  last_name:    'Baratheon',
  gravatar_url: 'http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm',
  online:       true
};
const agentTwo = {
  id:           3,
  name:         'Tyrion Lannister',
  first_name:   'Tyrion',
  last_name:    'Lannister',
  gravatar_url: 'http://www.gravatar.com/avatar/5f975bc3167e3b42e53bd541b1da8b8c?&d=mm',
  online:       true
};

const imState = {
  me: Immutable.Map({
    id:   1,
    name: 'Jon Snow'
  }),
  agents: Immutable.Map({
    [agentOne.id]: Immutable.Map(agentOne),
    [agentTwo.id]: Immutable.Map(agentTwo)
  })
};


storiesOf('Agent: IM', module)
  .addDecorator(story => css(story()))
  .add(
    'IMOverlay',
    () =>
      <IMOverlay {...imState} />
  )
;
