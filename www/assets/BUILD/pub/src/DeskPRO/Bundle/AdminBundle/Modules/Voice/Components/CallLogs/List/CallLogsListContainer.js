import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import CallLogsList from './CallLogsList';
import { loadPhoneCalls, openDialpad, deleteRecording } from '../../../Actions/callActions';
import { loadNumbers } from '../../../Actions/numberActions';
import { allNumbersSelector, isNumbersLoadedSelector } from '../../../Selectors/numbers';
import { allTicketsSelector } from '../../../../Application/Selectors/tickets';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  numbers:       allNumbersSelector(state),
  numbersLoaded: isNumbersLoadedSelector(state),
  tickets:       allTicketsSelector(state)
}))
class CallLogsListContainer extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func,
    numbersLoaded: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      calls:       null,
      pageCount:   1,
      liveUpdates: false,
      currentPage: 1
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;

    this.mounted = true;
    this.loadPageData();
    dispatch(loadNumbers());
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  openCallLog = (id) => {
    replaceRoute(`/voice_channel/call_logs/${id}`);
  };

  pageChange = ({ selected }) => {
    this.setState({ currentPage: selected + 1 }, () => this.loadPageData());
  };

  toggleLiveUpdates = () => {
    this.setState({
      liveUpdates: !this.state.liveUpdates
    }, () => {
      const pollingRequest = () => {
        const { liveUpdates, currentPage } = this.state;
        if (!this.mounted || !liveUpdates || currentPage > 1) {
          return;
        }

        this.loadPageData().then(
          () => setTimeout(pollingRequest, 5000),
          () => setTimeout(pollingRequest, 5000)
        );
      };

      setTimeout(pollingRequest, 5000);
    });
  };

  deleteRecording = (callId) => {
    this.props.dispatch(deleteRecording(callId)).then(() => this.loadPageData());
  };

  loadPageData() {
    const { dispatch } = this.props;
    const { currentPage } = this.state;
    const promise = dispatch(loadPhoneCalls(currentPage));
    promise.success(({ data, meta }) => {
      this.setState({
        calls:     Immutable.fromJS(data),
        pageCount: meta.pagination.total_pages
      });
    });

    return promise;
  }

  openDialpad = (number) => {
    this.props.dispatch(openDialpad(number));
  };

  render() {
    const { numbersLoaded } = this.props;
    const { calls } = this.state;

    if (calls === null || !numbersLoaded) {
      return <LoadingPage />;
    }

    return (
      <CallLogsList
        {...this.props}
        {...this.state}
        deleteRecording={this.deleteRecording}
        pageChange={this.pageChange}
        openCallLog={this.openCallLog}
        openDialpad={this.openDialpad}
        toggleLiveUpdates={this.toggleLiveUpdates}
      />
    );
  }
}

export default CallLogsListContainer;
