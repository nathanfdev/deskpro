import PropTypes from 'prop-types';
import React from 'react';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import QueueForm from './QueueForm';

class QueuePageForm extends React.Component {

  static propTypes = {
    queue:        PropTypes.object,
    onReturnBack: PropTypes.func.isRequired
  };

  render() {
    const { queue, onReturnBack } = this.props;

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title={queue ? 'Update queue' : 'Create new queue'} dividing />

        <QueueForm {...this.props} />
      </div>
    );
  }
}

export default QueuePageForm;
