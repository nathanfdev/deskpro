import { url } from '../helpers.js';

module.exports = {
  url: url('/tickets'),
  elements: {
    filtersTab: {
      locateStrategy: 'xpath',
      selector: '//ul/li/a[contains(., "Filters")]'
    },
    myTicketsFilter: {
      locateStrategy: 'xpath',
      selector: '//a/div[contains(., "My Tickets")]'
    }
  }
};
