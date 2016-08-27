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
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentFour = {
  id:           5,
  name:         'Duncan The Tall',
  first_name:   'Duncan',
  last_name:    'The Tall',
  last_seen:    '2016-08-26T06:56:45+0000',
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       false,
  avatar:       Immutable.Map({
    base_gravatar_url:   `http://lorempixel.com/22/22/people/?random=${random()}`,
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentFive = {
  id:           6,
  name:         'Daenerys Targaryen',
  first_name:   'Daenerys',
  last_name:    'Targaryen',
  last_seen:    '2016-08-26T06:56:45+0000',
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       true,
  avatar:       Immutable.Map({
    base_gravatar_url:   `http://lorempixel.com/22/22/people/?random=${random()}`,
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentSix = {
  id:           7,
  name:         'Jorah Mormont',
  first_name:   'Jorah',
  last_name:    'Mormont',
  last_seen:    '2016-08-26T06:56:45+0000',
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       false,
  avatar:       Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const depOne = {
  id:     1,
  title:  'Stark, Winter is Coming',
  agents: [1, 2, 3, 4, 5, 6, 7]
};

const depTwo = {
  id:     2,
  title:  'Lannister, Hear Me Roar!',
  agents: [1, 2, 3]
};

const depThree = {
  id:     3,
  title:  'Greyjoy, We Do Not Sow',
  agents: [1, 4, 2, 6]
};

const notifications = {
  [agentOne.id]:   3,
  [agentTwo.id]:   4,
  [agentThree.id]: 0,
  [agentFour.id]:  200,
  [agentFive.id]:  8500,
  [agentSix.id]:   1
};

const imState = {
  me: Immutable.Map({
    id:   1,
    name: 'Jon Snow'
  }),
  agents: Immutable.Seq({
    [agentOne.id]:   Immutable.Map(agentOne),
    [agentTwo.id]:   Immutable.Map(agentTwo),
    [agentThree.id]: Immutable.Map(agentThree),
    [agentFour.id]:  Immutable.Map(agentFour),
    [agentFive.id]:  Immutable.Map(agentFive),
    [agentSix.id]:   Immutable.Map(agentSix)
  }),
  departments: Immutable.Seq({
    [depOne.id]:   Immutable.Map(depOne),
    [depTwo.id]:   Immutable.Map(depTwo),
    [depThree.id]: Immutable.Map(depThree)
  }),
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
