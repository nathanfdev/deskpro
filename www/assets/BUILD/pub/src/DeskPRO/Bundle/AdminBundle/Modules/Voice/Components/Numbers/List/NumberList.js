import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import NumberTarget from '../../Common/NumberTarget/NumberTarget';
import VoiceTargetNameContainer from '../../Common/NumberTarget/VoiceTargetNameContainer';

class NumberHeader extends React.Component {

  render() {
    return (
      <SectionHeader
        title="Numbers"
        description="All your phone numbers and which users are associated with them (individually or via teams)."
        dividing
      />
    );
  }
}

class NumberRow extends React.Component {

  static propTypes = {
    number:       PropTypes.object,
    onEditNumber: PropTypes.func
  };

  onToggleOptions = (event) => {
    event.preventDefault();

    const { number, onEditNumber } = this.props;
    onEditNumber(number);
  };

  render() {
    const { number } = this.props;

    return (
      <div className="row" key={number.get('id')}>
        <div className="info">
          <div className="column location">
            <div className={classNames('flag-icon', `flag-icon-${number.get('country_code')}`)} />
          </div>
          <div className="column number">{number.get('number')}</div>
          <div className="column nickname">
            {number.get('nickname') ? number.get('nickname') : number.get('number')}
          </div>
          <div className="column targets">
            {number.get('target') &&
              <VoiceTargetNameContainer target={number.get('target')}>
                <NumberTarget withType />
              </VoiceTargetNameContainer>
            }
          </div>
          <div className="column press-options">
            {number.get('outbound_calls_enabled') &&
            <span className="press-option">
              Allow outbound calls from this number <i className="icon checkmark" />
            </span>}
          </div>
          <div className="column options-button">
            <a onClick={this.onToggleOptions}>
              <i className="fas fa-cog" />
            </a>
          </div>
          <div style={{ clear: 'both' }} />
        </div>
      </div>
    );
  }
}

class NumberList extends React.Component {

  static propTypes = {
    accounts:            PropTypes.object,
    numbers:             PropTypes.object,
    queues:              PropTypes.object,
    onGoToAccounts:      PropTypes.func,
    onAddNumber:         PropTypes.func,
    onAddExistingNumber: PropTypes.func,
    onEditNumber:        PropTypes.func
  };

  renderNoAccount() {
    const { onGoToAccounts } = this.props;

    return (
      <div className="page">
        <NumberHeader />

        You currently have no accounts.
        <br /><br />

        <button className="ui primary button" onClick={onGoToAccounts}>
          Open general settings
        </button>
      </div>
    );
  }

  renderEmpty() {
    const { onAddNumber, onAddExistingNumber } = this.props;

    return (
      <div className="page">
        <NumberHeader />

        You currently have no phone numbers.
        <br /><br />

        <button className="ui primary button" onClick={onAddNumber}>
          Add number
        </button>
        <button className="ui primary button" onClick={onAddExistingNumber}>
          Add existing number
        </button>
      </div>
    );
  }

  renderTable() {
    const { numbers, queues } = this.props;
    const { onAddNumber, onEditNumber, onAddExistingNumber } = this.props;

    return (
      <div className="page">
        <button className="ui right floated basic button" onClick={onAddNumber}>
          <i className="icon plus" />
          Add number
        </button>
        <button className="ui right floated basic button" onClick={onAddExistingNumber}>
          <i className="icon plus" />
          Add existing number
        </button>

        <NumberHeader />

        <div className="admin-list-table">
          <div className="row header">
            <div className="column location">Loc.</div>
            <div className="column number">Number</div>
            <div className="column nickname">Nickname</div>
            <div className="column targets">Target</div>
          </div>
          {numbers.sortBy(number => -number.get('id')).toArray().map(number =>
            <NumberRow
              key={number}
              number={number}
              queues={queues}
              onEditNumber={onEditNumber}
            />
          )}
        </div>
      </div>
    );
  }

  render() {
    const { accounts, numbers } = this.props;

    if (!accounts.size) {
      return this.renderNoAccount();
    } else if (numbers && numbers.size) {
      return this.renderTable();
    }

    return this.renderEmpty();
  }
}

export default NumberList;
