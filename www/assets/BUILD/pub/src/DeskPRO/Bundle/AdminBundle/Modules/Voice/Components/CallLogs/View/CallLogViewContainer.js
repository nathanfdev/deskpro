import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import CallLogView from './CallLogView';
import { loadPhoneCall, openDialpad } from '../../../Actions/callActions';
import { loadNumbers } from '../../../Actions/numberActions';
import { allNumbersSelector, isNumbersLoadedSelector } from '../../../Selectors/numbers';
import { allTicketsSelector } from '../../../../Application/Selectors/tickets';
import { allPeopleSelector } from '../../../../Application/Selectors/people';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  numbers:       allNumbersSelector(state),
  numbersLoaded: isNumbersLoadedSelector(state),
  tickets:       allTicketsSelector(state),
  people:        allPeopleSelector(state)
}))
class CallLogViewContainer extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func,
    params:        PropTypes.object,
    numbersLoaded: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      call: null
    };
  }

  componentDidMount() {
    const { dispatch, params } = this.props;

    dispatch(loadNumbers());

    const promise = dispatch(loadPhoneCall(params.callId));
    promise.success(({ data }) => {
      this.setState({
        call: Immutable.fromJS(data)
      });
    });
  }

  onReturnBack = () => {
    replaceRoute('/voice_channel/call_logs');
  };

  openDialpad = (number) => {
    this.props.dispatch(openDialpad(number));
  };

  render() {
    const { numbersLoaded } = this.props;
    const { call } = this.state;

    if (!call || !numbersLoaded) {
      return <LoadingPage />;
    }

    return (
      <CallLogView
        {...this.props}
        call={call}
        onReturnBack={this.onReturnBack}
        openDialpad={this.openDialpad}
      />
    );
  }
}

export default CallLogViewContainer;
