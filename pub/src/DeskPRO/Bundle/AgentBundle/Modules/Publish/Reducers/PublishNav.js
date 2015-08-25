import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/publishNavActions';

export default class PublishNav extends Reducer {
  getInitialState() {
    return {
      articles: {
        count: 42,
        nested: [
          {
            count: 13,
            group: 2,
            nested: [
              {
                count: 7,
                group: 3,
                nested: [
                  {count: 4, group: 4},
                  {count: 3, group: 5},
                ]
              },
              {
                count: 6,
                group: 6
              }
            ]
          },
          {
            count: 15,
            group: 7
          },
          {
            count: 14,
            group: 8,
            nested: [
              {
                count: 14,
                group: 9,
                nested: [
                  {count: 10, group: 10},
                  {count: 4, group: 11, nested: [
                    {count: 4, group: 13}
                  ]},
                ]
              }
            ]
          }
        ]
      },

      news: {
        count: 55,
        nested: [
          {
            count: 25,
            group: 2,
            nested: [
              {
                count: 7,
                group: 3,
                nested: [
                  {count: 4, group: 4},
                  {count: 3, group: 5},
                ]
              },
              {
                count: 6,
                group: 6
              }
            ]
          },
          {
            count: 14,
            group: 8
          }
        ]
      },

      downloads: {
        count: 7,
        nested: [
          {
            count: 13,
            group: 2,
            nested: [
              {
                count: 7,
                group: 3,
                nested: [
                  {count: 4, group: 4},
                  {count: 3, group: 5},
                ]
              },
              {
                count: 6,
                group: 6
              }
            ]
          },
          {
            count: 3,
            group: 7
          },
          {
            count: 2,
            group: 8
          }
        ]
      },


      categories: {
        article: {
          2: 'Getting Started',
          3: 'Agent area',
          4: 'Admin area',
          5: 'Group 5',
          6: 'Group 6',
          7: 'Group 7',
          8: 'DeskPRO Cloud',
          9: 'Installation',
          10: 'Configuration',
          11: 'Group 11',
          12: 'Group 12'
        },

        news: {
          2: 'All News',
          3: 'Hot News',
          4: 'Admin News',
          5: 'Group 5',
          6: 'Group 6',
          7: 'Group 7',
          8: 'DeskPRO News',
          9: 'Top News',
          10: 'Most popular news',
          11: 'News 11',
          12: 'News 12'
        },

        downloads: {
          2: 'All downloads',
          3: 'Agent downloads',
          4: 'Admin downloads',
          5: 'Downloads 5',
          6: 'Group 6',
          7: 'Group 7',
          8: 'DeskPRO Downloads',
          9: 'Recent',
          10: 'Trashed',
          11: 'Downloads 11',
          12: 'Downloads 12'
        }
      }
    };
  }

  registerHandlers() {
  }
}
