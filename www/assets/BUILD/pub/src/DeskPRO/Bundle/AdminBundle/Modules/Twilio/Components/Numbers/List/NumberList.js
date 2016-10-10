import React, { PropTypes } from 'react';
import classNames from 'classnames';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import NumberOptionsFormContainer from './NumberOptionsFormContainer';
import NumberTarget from '../../Common/NumberTarget/NumberTarget';

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
    number:     PropTypes.object,
    queues:     PropTypes.object,
    expanded:   PropTypes.bool,
    onCollapse: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      expanded: props.expanded || false
    };
  }

  onToggleOptions = (event) => {
    event.preventDefault();

    const { number, onCollapse } = this.props;

    onCollapse(number);
    this.setState({
      expanded: !this.state.expanded
    });
  };

  renderTarget() {
    const { number, queues } = this.props;
    const targetId = number.get('target_id');
    const targetType = number.get('target_type');

    switch (targetType) {
      case 'queue':
        return <NumberTarget name={`Queue: ${queues.get(targetId).get('name')}`} />;
      default:
        return null;
    }
  }

  render() {
    const { number, queues } = this.props;

    return (
      <div className="row" key={number.get('id')}>
        <div className="info">
          <div className="column location">
            <div className={classNames('flag-icon', `flag-icon-${number.get('country_code')}`)} />
          </div>
          <div className="column number">{number.get('number')}</div>
          {!this.state.expanded && <div className="column nickname">{number.get('nickname')}</div>}
          {!this.state.expanded &&
            <div className="column targets">
              {number.get('target_id') > 0 && this.renderTarget()}
            </div>}
          <div className="column options-button">
            <a onClick={this.onToggleOptions}>
              <i className="fa fa-gear" />
            </a>
          </div>
        </div>
        {this.state.expanded &&
          <div className="column options">
            <NumberOptionsFormContainer number={number} queues={queues} />
          </div>
        }
      </div>
    );
  }
}

class NumberList extends React.Component {

  static propTypes = {
    accounts:            PropTypes.object,
    numbers:             PropTypes.object,
    queues:              PropTypes.object,
    expandedNumber:      PropTypes.number,
    onGoToAccounts:      PropTypes.func,
    onAddNumber:         PropTypes.func,
    onAddExistingNumber: PropTypes.func,
    onCollapseNumber:    PropTypes.func
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
    const { numbers, queues, expandedNumber } = this.props;
    const { onAddNumber, onAddExistingNumber, onCollapseNumber } = this.props;

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

        <div className="twilio-list-table">
          <div className="row header">
            <div className="column location">Loc.</div>
            <div className="column number">Number</div>
            <div className="column nickname">Nickname</div>
            <div className="column targets">Target</div>
          </div>
          {numbers.sortBy(number => -number.get('id')).map(number =>
            <NumberRow
              number={number}
              queues={queues}
              expanded={expandedNumber === number.get('id')}
              onCollapse={onCollapseNumber}
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
