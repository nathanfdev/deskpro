import React, { PropTypes } from 'react';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import AudioWidget from '../../Common/AudioWidget/AudioWidget';
import AudioWidgetContainer from './AudioWidgetContainer';

class QueueHeader extends React.Component {

  render() {
    return (
      <SectionHeader
        title="Queue Settings"
        description="Add and manage queues that can be targets for numbers and IVRs."
        dividing
      />
    );
  }
}

class QueueRow extends React.Component {

  static propTypes = {
    queue:       PropTypes.object,
    onEditQueue: PropTypes.func
  };

  onEditQueue = (event) => {
    event.preventDefault();

    const { queue, onEditQueue } = this.props;
    onEditQueue(queue);
  };

  render() {
    const { queue } = this.props;

    return (
      <div className="row" key={queue.get('id')}>
        <div className="info">
          <div className="column queue-name">{queue.get('name')}</div>
          <div className="column asset">
            <AudioWidgetContainer queue={queue} propName="greet_asset" >
              <AudioWidget />
            </AudioWidgetContainer>
          </div>
          <div className="column asset">
            <AudioWidgetContainer queue={queue} propName="loop_asset">
              <AudioWidget />
            </AudioWidgetContainer>
          </div>
          <div className="column asset">
            <AudioWidgetContainer queue={queue} propName="voicemail_asset">
              <AudioWidget />
            </AudioWidgetContainer>
          </div>
          <div className="column options-button">
            <a onClick={this.onEditQueue}>
              <i className="fa fa-gear" />
            </a>
          </div>
        </div>
      </div>
    );
  }
}

class QueueList extends React.Component {

  static propTypes = {
    queues:      PropTypes.object,
    onAddQueue:  PropTypes.func,
    onEditQueue: PropTypes.func
  };

  renderEmpty() {
    return (
      <div className="page">
        <QueueHeader />

        You currently have no queues.
        <br /><br />

        <button className="ui primary button" onClick={this.props.onAddQueue}>
          Add queue
        </button>
      </div>
    );
  }

  renderTable() {
    const { queues, onAddQueue, onEditQueue } = this.props;

    return (
      <div className="page">
        <button className="ui right floated basic button" onClick={onAddQueue}>
          <i className="icon plus" />
          Add queue
        </button>

        <QueueHeader />

        <div className="twilio-list-table">
          <div className="row header">
            <div className="column queue-name">Name</div>
            <div className="column asset">Greet</div>
            <div className="column asset">Loop</div>
            <div className="column asset">Voicemail</div>
          </div>
          {queues.map(queue => <QueueRow queue={queue} onEditQueue={onEditQueue} />)}
        </div>
      </div>
    );
  }

  render() {
    const { queues } = this.props;

    return queues && queues.size ? this.renderTable() : this.renderEmpty();
  }
}

export default QueueList;
