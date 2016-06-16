module.exports = {
  url: 'http://dp.lo/new-agent/tickets',
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
