import Immutable from 'immutable';
import moment from 'moment';

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
  last_seen:    moment().subtract(10, 'minutes').format(),
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
  last_seen:    moment().subtract(2, 'hours').format(),
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
  last_seen:    moment().subtract(7, 'days').format(),
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       false,
  avatar:       Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentSeven = {
  id:           8,
  name:         'Jeor Mormont',
  first_name:   'Jeor',
  last_name:    'Mormont',
  last_seen:    moment().subtract(7, 'days').format(),
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       false,
  avatar:       Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentEight = {
  id:           9,
  name:         'Jon Snow',
  first_name:   'Jon',
  last_name:    'Snow',
  last_seen:    moment().subtract(7, 'days').format(),
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       false,
  avatar:       Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentNine = {
  id:           10,
  name:         'Jojen Reed',
  first_name:   'Jojen',
  last_name:    'Reed',
  last_seen:    moment().subtract(7, 'minutes').format(),
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       true,
  avatar:       Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentTen = {
  id:           11,
  name:         'Mira Reed',
  first_name:   'Mira',
  last_name:    'Reed',
  last_seen:    moment().subtract(7, 'minutes').format(),
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       true,
  avatar:       Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentEleven = {
  id:           12,
  name:         'Brandon Stark',
  first_name:   'Brandon',
  last_name:    'Stark',
  last_seen:    moment().subtract(1, 'minutes').format(),
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       true,
  avatar:       Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const agentTwelve = {
  id:           13,
  name:         'Arya Stark',
  first_name:   'Arya',
  last_name:    'Stark',
  last_seen:    moment().subtract(300, 'days').format(),
  gravatar_url: `http://lorempixel.com/22/22/people/?random=${random()}`,
  online:       true,
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

const teamOne = {
  id:     1,
  name:   'Tarley',
  agents: [1, 4],
  avatar: Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const teamTwo = {
  id:     2,
  name:   'Martell',
  agents: [1, 4, 6],
  avatar: Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const teamThree = {
  id:     3,
  name:   'Clegane',
  agents: [1, 2, 3, 4, 6],
  avatar: Immutable.Map({
    default_url_pattern: 'http://localhost/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1'
  })
};

const chatOne = {
  id:                '1',
  chat_type:         'agent',
  agents:            Immutable.List([1, 6]),
  departments:       [],
  agent_teams:       [],
  date_created:      moment().subtract(2, 'hours').format(),
  date_last_message: moment().subtract(3, 'minutes').format()
};

const chatTwo = {
  id:                2,
  chat_type:         'department',
  agents:            [],
  departments:       Immutable.List([2]),
  agent_teams:       [],
  date_created:      moment().subtract(3, 'days').format(),
  date_last_message: moment().subtract(1, 'day').format()
};

const chatThree = {
  id:                3,
  chat_type:         'agent',
  agents:            Immutable.List([1, 7]),
  departments:       [],
  agent_teams:       [],
  date_created:      moment().subtract(12, 'days').format(),
  date_last_message: moment().subtract(2, 'hours').format()
};


const chatFour = {
  id:                4,
  chat_type:         'team',
  agents:            [],
  departments:       [],
  agent_teams:       Immutable.List([2]),
  date_created:      moment().subtract(12, 'days').format(),
  date_last_message: moment().subtract(2, 'hours').format()
};


const notifications = {
  [agentOne.id]:   3,
  [agentTwo.id]:   4,
  [agentThree.id]: 0,
  [agentFour.id]:  200,
  [agentFive.id]:  8500,
  [agentSix.id]:   1
};

const chats = {
  [chatOne.id]:   Immutable.Map(chatOne),
  [chatTwo.id]:   Immutable.Map(chatTwo),
  [chatThree.id]: Immutable.Map(chatThree),
  [chatFour.id]:  Immutable.Map(chatFour)
};

export const imState = {
  groups: Immutable.fromJS({}),
  counts: { nested: 0 },
  me:     Immutable.Map({
    id:   1,
    name: 'Jon Snow'
  }),
  agents: Immutable.Seq({
    [agentOne.id]:    Immutable.Map(agentOne),
    [agentTwo.id]:    Immutable.Map(agentTwo),
    [agentThree.id]:  Immutable.Map(agentThree),
    [agentFour.id]:   Immutable.Map(agentFour),
    [agentFive.id]:   Immutable.Map(agentFive),
    [agentSix.id]:    Immutable.Map(agentSix),
    [agentSeven.id]:  Immutable.Map(agentSeven),
    [agentEight.id]:  Immutable.Map(agentEight),
    [agentNine.id]:   Immutable.Map(agentNine),
    [agentTen.id]:    Immutable.Map(agentTen),
    [agentEleven.id]: Immutable.Map(agentEleven),
    [agentTwelve.id]: Immutable.Map(agentTwelve)
  }),
  departments: Immutable.Seq({
    [depOne.id]:   Immutable.Map(depOne),
    [depTwo.id]:   Immutable.Map(depTwo),
    [depThree.id]: Immutable.Map(depThree)
  }),
  teams: Immutable.Seq({
    [teamOne.id]:   Immutable.Map(teamOne),
    [teamTwo.id]:   Immutable.Map(teamTwo),
    [teamThree.id]: Immutable.Map(teamThree)
  }),
  notifications:     Immutable.Seq(notifications),
  chats:             Immutable.Seq(chats),
  recentLoaded:      true,
  teamsLoaded:       true,
  departmentsLoaded: true,
  agentsLoaded:      true,
  loadingMessages:   false
};

const messageOne = {
  chat:         1,
  date_created: moment().subtract(71, 'minutes').format(),
  id:           1,
  message:      '<p>Ut pretium risus neque maximus</p>',
  metadata:     [],
  person:       1,
  person_name:  'Admin Admin',
  status:       1,
  timestamp:    1472383153,
  uuid:         'a6e789a6-cd72-4a1b-888c-c93e1868c6b3'
};

const messageTwo = {
  chat:         1,
  date_created: moment().subtract(70, 'minutes').format(),
  id:           2,
  message:      '<p>Zombie ipsum brains reversus ab cerebellum viral inferno, brein nam rick mend grimes malum ' +
                'cerveau cerebro. De carne cerebro lumbering animata cervello corpora quaeritis. Summus thalamus ' +
                'brains sit​​, morbo basal ganglia vel maleficia?</p>',
  metadata:    [],
  person:      6,
  person_name: 'Daenerys Targaryen',
  status:      1,
  timestamp:   1472383154,
  uuid:        'a6e789a6-cd72-4a1b-888c-c93e1868c6b4'
};

const messageThree = {
  chat:         1,
  date_created: moment().subtract(1, 'hour').format(),
  id:           3,
  message:      '<p>De braaaiiiins apocalypsi gorger omero prefrontal cortex undead survivor fornix dictum mauris.</p>',
  metadata:     [],
  person:       1,
  person_name:  'Admin Admin',
  status:       1,
  timestamp:    1472383155,
  uuid:         'b6e789a6-cd72-4a1b-888c-c93e1868c6b5'
};

const messageFour = {
  chat:         1,
  date_created: moment().subtract(12, 'minutes').format(),
  id:           4,
  message:      '<p>Hi brains mindless mortuis limbic cortex soulless creaturas optic nerve, imo evil braaiinns ' +
                'stalking monstra hypothalamus adventus resi hippocampus dentevil vultus brain comedat cerebella ' +
                'pitiutary gland viventium.</p>',
  metadata:    [],
  person:      6,
  person_name: 'Daenerys Targaryen',
  status:      1,
  timestamp:   1472383156,
  uuid:        'c6e789a6-cd72-4a1b-888c-c93e1868c6b6'
};

export const messages = Immutable.Map({
  chatMessages: Immutable.Map({
    [chatOne.id]: {
      messages: Immutable.List([
        messageOne,
        messageTwo,
        messageThree,
        messageFour
      ])
    }
  })
});
