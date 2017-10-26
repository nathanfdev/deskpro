import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import CallLogsList from './CallLogsList';
import { loadPhoneCalls, openDialpad } from '../../../Actions/callActions';
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
      calls:     null,
      pageCount: 1
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;

    this.loadPageData();
    dispatch(loadNumbers());
  }

  onOpenCallLog = (id) => {
    replaceRoute(`/voice_channel/call_logs/${id}`);
  };

  onPageChange = ({ selected }) => {
    this.loadPageData(selected + 1);
  };

  loadPageData(page = 1) {
    const { dispatch } = this.props;
    const promise = dispatch(loadPhoneCalls(page));
    promise.success(({ data, meta }) => {
      this.setState({
        calls:     Immutable.fromJS(data),
        pageCount: meta.pagination.total_pages
      });
    });
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
        onPageChange={this.onPageChange}
        onOpenCallLog={this.onOpenCallLog}
        openDialpad={this.openDialpad}
      />
    );
  }
}

export default CallLogsListContainer;
