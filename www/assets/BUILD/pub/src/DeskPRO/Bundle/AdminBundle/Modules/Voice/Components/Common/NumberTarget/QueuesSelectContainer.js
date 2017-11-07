import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Modal from 'DeskPRO/Component/Semantic/Modal';
import { Select, RecordsChoiceWrapper } from 'DeskPRO/Component/Semantic/ReactForm';
import { allQueuesSelector } from '../../../Selectors/queue';
import NewQueueModalContainer from '../../Queues/Form/NewQueueModalContainer';

@connect(state => ({
  queues: allQueuesSelector(state)
}))
class QueuesSelectContainer extends React.Component {

  static propTypes = {
    queues: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      formOpened: false
    };
  }

  onAddNew = () => {
    this.setState({
      formOpened: true
    });
  };

  onClose = () => {
    this.setState({
      formOpened: false
    });
  };

  render() {
    const { queues } = this.props;
    const { formOpened } = this.state;

    return (
      <div>
        <RecordsChoiceWrapper records={queues}>
          <Select
            {...this.props}
            clearable={false}
            addNewLabel="Add a new queue..."
            onAddNew={this.onAddNew}
          />
        </RecordsChoiceWrapper>
        <Modal
          isOpen={formOpened}
          onClose={this.onClose}
          title="New Queue"
          contentStyles={{ top: '10%', bottom: '10%' }}
        >
          <NewQueueModalContainer onClose={this.onClose} />
        </Modal>
      </div>
    );
  }
}

export default QueuesSelectContainer;
