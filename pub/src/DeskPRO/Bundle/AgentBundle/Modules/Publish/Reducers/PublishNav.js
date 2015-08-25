import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/publishNavActions';

export default class PublishNav extends Reducer {
  getInitialState() {
    return {
      articles: {
        total: 42,
        nested: [
          {
            total: 13,
            group: 2,
            nested: [
              {
                total: 7,
                group: 3,
                nested: [
                  {total: 4, group: 4},
                  {total: 3, group: 5},
                ]
              },
              {
                total: 6,
                group: 6
              }
            ]
          },
          {
            total: 15,
            group: 7
          },
          {
            total: 14,
            group: 8,
            nested: [
              {
                total: 14,
                group: 9,
                nested: [
                  {total: 10, group: 10},
                  {total: 4, group: 11, nested: [
                    {total: 4, group: 13}
                  ]},
                ]
              }
            ]
          }
        ]
      },

      news: {
        total: 55,
        nested: [
          {
            total: 25,
            group: 2,
            nested: [
              {
                total: 7,
                group: 3,
                nested: [
                  {total: 4, group: 4},
                  {total: 3, group: 5},
                ]
              },
              {
                total: 6,
                group: 6
              }
            ]
          },
          {
            total: 14,
            group: 8
          }
        ]
      },

      downloads: {
        total: 7,
        nested: [
          {
            total: 13,
            group: 2,
            nested: [
              {
                total: 7,
                group: 3,
                nested: [
                  {total: 4, group: 4},
                  {total: 3, group: 5},
                ]
              },
              {
                total: 6,
                group: 6
              }
            ]
          },
          {
            total: 3,
            group: 7
          },
          {
            total: 2,
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
