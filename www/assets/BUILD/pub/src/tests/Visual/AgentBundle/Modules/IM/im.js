import React from 'react';
import { storiesOf } from '@kadira/storybook';
import IMOverlay from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMOverlay';
import { css } from 'Visual/decorators';
import Immutable from 'immutable';

function random() {
  return Math
          .random()
          .toString(36)
          .replace(/[^a-z]+/g, '')
          .substr(0, 5)
    ;
}

const agentOne = {
  id:           2,
  name:         'Robert Baratheon',
  first_name:   'Robert',
  last_name:    'Baratheon',
  last_seen:    '2016-08-26T06:56:45+0000',
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       true,
  avatar:       Immutable.Map({
    base_gravatar_url:   `http://lorempixel.com/22/22/people/?random=${random()}`,
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};
const agentTwo = {
  id:           3,
  name:         'Tyrion Lannister',
  first_name:   'Tyrion',
  last_name:    'Lannister',
  last_seen:    '2016-08-26T06:56:45+0000',
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       true,
  avatar:       Immutable.Map({
    base_gravatar_url:   `http://lorempixel.com/22/22/people/?random=${random()}`,
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};
const agentThree = {
  id:           4,
  name:         'Howland Reed',
  first_name:   'Howland',
  last_name:    'Reed',
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       false,
  avatar:       Immutable.Map({
    base_gravatar_url:   `http://lorempixel.com/22/22/people/?random=${random()}`,
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentFour = {
  id:           5,
  name:         'Duncan The Tall',
  first_name:   'Duncan',
  last_name:    'The Tall',
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       false,
  avatar:       Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const depOne = {
  id:    1,
  title: 'Stark, Winter is Coming'
};

const depTwo = {
  id:    2,
  title: 'Lannister, Hear Me Roar!'
};

const depThree = {
  id:    3,
  title: 'Greyjoy, We Do Not Sow'
};

const notifications = {
  [agentOne.id]:   3,
  [agentTwo.id]:   4,
  [agentThree.id]: 0,
  [agentFour.id]:  200
};

const imState = {
  me: Immutable.Map({
    id:   1,
    name: 'Jon Snow'
  }),
  agents: Immutable.List([
    Immutable.Map(agentOne),
    Immutable.Map(agentTwo),
    Immutable.Map(agentThree),
    Immutable.Map(agentFour)
  ]),
  departments: Immutable.List([
    Immutable.Map(depOne),
    Immutable.Map(depTwo),
    Immutable.Map(depThree)
  ]),
  notifications: Immutable.fromJS(notifications)
};


storiesOf('Agent: IM', module)
  .addDecorator(story => css(story()))
  .add(
    'IMOverlay',
    () =>
      <IMOverlay {...imState} />
  )
;
