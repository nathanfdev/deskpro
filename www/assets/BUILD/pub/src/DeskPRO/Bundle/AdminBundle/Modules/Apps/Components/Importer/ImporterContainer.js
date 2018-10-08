import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import { loadAll } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { getImportStatus } from '../../Actions/importerActions';
import Importer from './Importer';
import { replaceRoute } from '../../../../Services/history';
import * as Sources from './Sources/index';
import { allImporterLogsSelector, isImporterLogsLoadedSelector } from '../../Selectors/importerLogs';

export const importerSources = {
  kayako: {
    title:       'Kayako',
    description: 'Import from your on-premise Kayako helpdesk.',
    component:   Sources.ImporterSourceKayako,
    steps:       ['article_category', 'article', 'news', 'person', 'ticket', 'organization', 'setting']
  },
  zendesk: {
    title:       'Zendesk',
    description: 'Import from a Zendesk helpdesk.',
    component:   Sources.ImporterSourceZendesk,
    steps:       ['article_category', 'article', 'person', 'ticket', 'organization']
  },
  // todo disabled for now
  // osticket: {
  //  title:       'osTicket',
  //  description: 'Import from your on-premise osTicket helpdesk.',
  //  component:   Sources.ImporterSourceOsTicket,
  //  steps:       ['article_category', 'article', 'news', 'person', 'ticket', 'organization', 'setting']
  // },
  // advanced: {
  //  title:       'Advanced',
  //  description: 'Import data from standard format JSON files.',
  //  component:   Sources.ImporterSourceAdvanced,
  //  steps:       ['article_category', 'article', 'news', 'person', 'ticket', 'organization', 'setting']
  // }
};

export const stepTitles = {
  article:          'Articles',
  article_category: 'Article categories',
  news:             'News',
  organization:     'Organizations',
  person:           'People',
  ticket:           'Tickets',
  setting:          'Settings'
};

@connect(state => ({
  logs:       allImporterLogsSelector(state),
  logsLoaded: isImporterLogsLoadedSelector(state)
}))
class ImporterContainer extends React.Component {

  static propTypes = {
    dispatch:   PropTypes.func,
    logs:       PropTypes.array,
    logsLoaded: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      loading: true
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAll('ImportLog'));
    const promise = dispatch(getImportStatus());
    promise.then(
      () => {
        replaceRoute('/apps/importer/status');
      },
      () => {
        this.setState({
          loading: false
        });
      }
    );
  }

  render() {
    const { logs, logsLoaded } = this.props;
    const { loading } = this.state;
    if (loading || !logsLoaded) {
      return <LoadingPage />;
    }

    return <Importer sources={importerSources} logs={logs} />;
  }
}

export default ImporterContainer;
