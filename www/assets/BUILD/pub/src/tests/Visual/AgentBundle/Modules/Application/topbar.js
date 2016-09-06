import React from 'react';
import Isvg from 'react-inlinesvg';
import Immutable from 'immutable';
import { storiesOf, action } from '@kadira/storybook';
import User from 'DeskPRO/Bundle/AgentBundle/Modules/TopBar/Components/User';
import AddButton from 'DeskPRO/Bundle/AgentBundle/Modules/TopBar/Components/AddButton';
import Chat from 'DeskPRO/Bundle/AgentBundle/Modules/TopBar/Components/Chat';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import recentSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/topbar/recent.svg';
import viewsSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/topbar/views.svg';
import notificationsSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/topbar/notifications.svg';
import teleOperator from '../../../Resources/teleoperator.jpg';
import { css } from '../../../decorators';

const agents = [
  {
    id:                        512,
    picture_blob:              null,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/79cfcfdf770db09a40fca15bb845fef1?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: '',
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'web.person',
    name:                      'Corporate Content',
    first_name:                'Corporate',
    last_name:                 'Content',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              null,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'UTC',
    date_created:              '2016-07-21T09:44:34+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [],
    agent_groups:              [],
    labels:                    [],
    primary_email:             'content.publisher@deskprodemo.com',
    emails:                    [
      'content.publisher@deskprodemo.com'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         null,
      base_gravatar_url:   'https://secure.gravatar.com/avatar/79cfcfdf770db09a40fca15bb845fef1'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 66,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        11,
    picture_blob:              12,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/7c7a55550e8a3b4fc038b83fd142b1b7?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Jevon Bode',
    first_name:                'Jevon',
    last_name:                 'Bode',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              65,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'Europe/London',
    date_created:              '2015-09-27T14:20:36+0000',
    date_last_login:           '2016-08-04T16:44:04+0000',
    browser:                   null,
    user_groups:               [],
    agent_groups:              [],
    labels:                    [
      'kertzmann ltd',
      'reynolds-boyer',
      'swaniawski, hamill and daugherty'
    ],
    primary_email: 'harley34@example.net',
    emails:        [
      'harley34@example.net'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/12HBBAKKZSBKNCWWY0/mathieu_kassovitz1.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/7c7a55550e8a3b4fc038b83fd142b1b7'
    },
    online:        true,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 63,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        10,
    picture_blob:              11,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/da5d33f1edca0378f4158f6c94d35925?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Jessyca Krajcik',
    first_name:                'Jessyca',
    last_name:                 'Krajcik',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              70,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'America/Santarem',
    date_created:              '2016-01-28T07:03:06+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [],
    agent_groups:              [],
    labels:                    [
      'cole, hammes and mosciski',
      'hamill-conn',
      'not_user',
      'vonrueden inc'
    ],
    primary_email: 'frieda89@example.net',
    emails:        [
      'frieda89@example.net'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/11XNYDJQNCTMGKCPN0/michael_jackson.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/da5d33f1edca0378f4158f6c94d35925'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 66,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        9,
    picture_blob:              10,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/5ba165b80207a9adbf9f43683f54a479?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Earnestine Pollich',
    first_name:                'Earnestine',
    last_name:                 'Pollich',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              18,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'America/Regina',
    date_created:              '2015-10-12T03:46:54+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [],
    agent_groups:              [],
    labels:                    [
      'kiehn-torp',
      'lueilwitz, morar and stamm',
      'metz, hoppe and connelly'
    ],
    primary_email: 'mayer.daniella@example.com',
    emails:        [
      'mayer.daniella@example.com'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/10BKQZMTGAHZGXRBA0/b_obama.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/5ba165b80207a9adbf9f43683f54a479'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 62,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        8,
    picture_blob:              9,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/5323617c52a452c52bc03d7b2c0c36b9?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Zakary Schaefer',
    first_name:                'Zakary',
    last_name:                 'Schaefer',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              19,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'Pacific/Fiji',
    date_created:              '2016-07-20T23:08:55+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [],
    agent_groups:              [],
    labels:                    [
      'feeney, hessel and lakin',
      'thompson-legros',
      'walker-mraz'
    ],
    primary_email: 'edgardo.bernier@example.net',
    emails:        [
      'edgardo.bernier@example.net'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/9CWTBWABJKCYHRRZ0/b_obama.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/5323617c52a452c52bc03d7b2c0c36b9'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 62,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        7,
    picture_blob:              8,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/6d100317173e4c9dd9287de31b2dee28?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Amelie Bode',
    first_name:                'Amelie',
    last_name:                 'Bode',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              19,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'Asia/Bahrain',
    date_created:              '2015-07-27T08:52:50+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [
      5,
      6
    ],
    agent_groups: [],
    labels:       [
      'bergnaum-gleichner'
    ],
    primary_email: 'myrtis.schaefer@example.org',
    emails:        [
      'myrtis.schaefer@example.org'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/8RPTSAJNBBQTQXMN0/mathieu_kassovitz1.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/6d100317173e4c9dd9287de31b2dee28'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 68,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        6,
    picture_blob:              7,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/59719175aa164e45edba17beafa1a5ba?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Orland Stoltenberg',
    first_name:                'Orland',
    last_name:                 'Stoltenberg',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              71,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'Asia/Yekaterinburg',
    date_created:              '2016-01-10T22:34:00+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [
      6
    ],
    agent_groups: [],
    labels:       [
      'rath inc'
    ],
    primary_email: 'dominique.nikolaus@example.org',
    emails:        [
      'dominique.nikolaus@example.org'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/7ZQGSYPAXRBQAMPT0/kate_middleton.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/59719175aa164e45edba17beafa1a5ba'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 61,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        5,
    picture_blob:              6,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/9d011d2cab877b96ed7a7ab4d21e489f?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Ludwig Kuhn',
    first_name:                'Ludwig',
    last_name:                 'Kuhn',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              3,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'Europe/Isle_of_Man',
    date_created:              '2016-03-27T14:52:40+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [],
    agent_groups:              [],
    labels:                    [
      'price and sons'
    ],
    primary_email: 'ibrahim32@example.net',
    emails:        [
      'ibrahim32@example.net'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/6TJQNPXWXCBDKXQS0/stanley_kubrick.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/9d011d2cab877b96ed7a7ab4d21e489f'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 76,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        4,
    picture_blob:              5,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/76fd0bd481a1d39bc57d1044f6d7c239?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Isaias Schuster',
    first_name:                'Isaias',
    last_name:                 'Schuster',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              46,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'Asia/Dhaka',
    date_created:              '2015-09-12T05:32:26+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [],
    agent_groups:              [],
    labels:                    [
      'deckow, homenick and eichmann',
      'dickens-abbott',
      'mitchell inc',
      'price, denesik and dickinson'
    ],
    primary_email: 'rodolfo.stark@example.com',
    emails:        [
      'rodolfo.stark@example.com'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/5RCWTDHAQKKZWQJT0/mathieu_kassovitz1.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/76fd0bd481a1d39bc57d1044f6d7c239'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 59,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        3,
    picture_blob:              4,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/2b6c6697dd29f737812cd0bce2aced26?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Shanny Swift',
    first_name:                'Shanny',
    last_name:                 'Swift',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              67,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'Asia/Kathmandu',
    date_created:              '2015-09-20T22:37:13+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [],
    agent_groups:              [],
    labels:                    [
      'lueilwitz, morar and stamm'
    ],
    primary_email: 'christopher40@example.org',
    emails:        [
      'christopher40@example.org'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/4QNAQGAQTKARATKZ0/stanley_kubrick.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/2b6c6697dd29f737812cd0bce2aced26'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 50,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        2,
    picture_blob:              3,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/3de89bcffefcdfb6a00a62dd14f46302?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 false,
    can_billing:               false,
    disable_autoresponses:     false,
    disable_autoresponses_log: null,
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'api.dev.1469094258',
    name:                      'Letitia Balistreri',
    first_name:                'Letitia',
    last_name:                 'Balistreri',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              65,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'Antarctica/Syowa',
    date_created:              '2015-12-21T20:02:04+0000',
    date_last_login:           null,
    browser:                   null,
    user_groups:               [
      5,
      6
    ],
    agent_groups: [],
    labels:       [
      'bruen and sons',
      'cassin and sons',
      'ferry, huels and rippin'
    ],
    primary_email: 'dimitri.auer@example.net',
    emails:        [
      'dimitri.auer@example.net'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         'http://deskpro5.local/file.php/size/{{IMG_SIZE}}/size-fit/3WSYHDCTTRZYHHXP0/borat.jpg',
      base_gravatar_url:   'https://secure.gravatar.com/avatar/3de89bcffefcdfb6a00a62dd14f46302'
    },
    online:        false,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 46,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  },
  {
    id:                        1,
    picture_blob:              null,
    disable_picture:           false,
    gravatar_url:              'http://www.gravatar.com/avatar/92d1cbad7e421b13f09ec01655afbe29?&d=mm',
    is_contact:                false,
    is_user:                   true,
    is_agent:                  true,
    was_agent:                 false,
    can_agent:                 true,
    can_admin:                 true,
    can_billing:               true,
    disable_autoresponses:     false,
    disable_autoresponses_log: '',
    is_confirmed:              true,
    is_deleted:                false,
    is_disabled:               false,
    creation_system:           'web.person',
    name:                      'Julien Ducro',
    first_name:                'Julien',
    last_name:                 'Ducro',
    title_prefix:              '',
    override_display_name:     '',
    summary:                   '',
    language:                  1,
    organization:              null,
    organization_position:     '',
    organization_manager:      false,
    timezone:                  'Europe/London',
    date_created:              '2016-07-21T09:44:17+0000',
    date_last_login:           '2016-08-04T12:05:39+0000',
    browser:                   'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0',
    user_groups:               [
      5,
      6,
      7
    ],
    agent_groups: [
      3
    ],
    labels: [
      'cole, hammes and mosciski'
    ],
    primary_email: 'julien.ducro@deskpro.com',
    emails:        [
      'julien.ducro@deskpro.com'
    ],
    avatar: {
      default_url_pattern: 'http://deskpro5.local/file.php/avatar/{{IMG_SIZE}}/default.jpg?size-fit=1',
      url_pattern:         null,
      base_gravatar_url:   'https://secure.gravatar.com/avatar/92d1cbad7e421b13f09ec01655afbe29'
    },
    online:        true,
    last_seen:     null,
    phone_numbers: [],
    tickets_count: 73,
    chats_count:   0,
    fields:        {},
    contact_data:  [],
    teams:         [],
    primary_team:  null
  }
];

storiesOf('App: top bar', module)
  .addDecorator(story => css(story()))
  .add(
    'Top bar',
    () => <div id="react_dp_agent_top_bar">
      <TopBar>
        <TopBarItem classes={['search-box legacy-omnibox']}>
          <SearchBox onUserInput={action('Search')} placeholder="Search ..." />
        </TopBarItem>
        <TopBarItem classes={['recent']}>
          <Isvg src={recentSvg} />
        </TopBarItem>
        <AddButton />
        <TopBarRightMenu>
          <TopBarItem classes={['views']}>
            <Isvg src={viewsSvg} />
          </TopBarItem>
          <TopBarItem>
            <TopBarNotificationIcon
              elementId="notifications"
              svg={notificationsSvg}
              count="2"
            />
          </TopBarItem>
          <TopBarItem>
            <User src={teleOperator} />
            <Chat
              agents={Immutable.fromJS(agents)}
              onlineAgents={['1', '8', '6']}
              updateVolume={action('Update volume')}
            />
          </TopBarItem>
        </TopBarRightMenu>
      </TopBar>
    </div>
  )
;
