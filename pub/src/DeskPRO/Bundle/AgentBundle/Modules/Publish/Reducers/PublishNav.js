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
        }
      }
    };
  }

  registerHandlers() {
  }
}
