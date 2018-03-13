import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import { getImportStatus } from '../../Actions/importerActions';
import Importer from './Importer';
import { replaceRoute } from '../../../../Services/history';
import * as Sources from './Sources/index';

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

@connect()
class ImporterContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      loading: true
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;
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
    const { loading } = this.state;
    if (loading) {
      return <LoadingPage />;
    }

    return <Importer sources={importerSources} />;
  }
}

export default ImporterContainer;
