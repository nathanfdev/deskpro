import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import NumberOptionsForm from './NumberOptionsForm';
import { editNumber } from '../../../Actions/numberActions';
import { replaceRoute } from '../../../../../Services/history';

@connect()
class NumberOptionsFormContainer extends React.Component {

  static propTypes = {
    number:   PropTypes.object,
    dispatch: PropTypes.func
  };

  onSubmit = (data) => {
    const { number, dispatch } = this.props;
    dispatch(editNumber(number.get('id'), data));
  };

  onAddQueue = () => {
    replaceRoute('/voice_channel/queues/new');
  };

  render() {
    return (
      <NumberOptionsForm
        {...this.props}
        onSubmit={this.onSubmit}
        onAddQueue={this.onAddQueue}
      />
    );
  }
}

export default NumberOptionsFormContainer;
